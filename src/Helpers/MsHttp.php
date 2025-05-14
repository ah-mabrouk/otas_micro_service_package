<?php

namespace Solutionplus\MicroService\Helpers;

use Closure;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Solutionplus\MicroService\Models\MicroServiceMap;

class MsHttp
{
    public $cipher;
    public $headers;
    public $protocol;
    public $microservice;
    public $microserviceName;

    protected function __construct(string $microserviceName, string $origin = '')
    {
        $this->cipher = 'aes-256-cbc';
        $this->microserviceName = $microserviceName;
        $this->microservice = $origin == '' ? $this->destinationMicroService($microserviceName) : new MicroServiceMap([
            'name' => \strtolower($microserviceName),
            'display_name' => $microserviceName,
            'origin' => $origin,
        ]);
        $this->protocol = $this->protocol();
    }

    protected static function destinationMicroService(string $microserviceName)
    {
        $microService = self::firstMsBy('name', $microserviceName);
        switch (true) {
            case (! $microService) :
                abort(503, 'the service you try to communicate with is not exist yet in linked services list');
            case (! $microService->origin) :
                abort(503, 'can\'t find the selected service origin');
            case ((! $microService->destination_key)) :
                abort(503, 'can\'t find the selected service key');
            default :
                return $microService;
        }
    }

    protected function protocol()
    {
        return config('microservice.secure_requests_only') == 'true' ? 'https://' : 'http://';
    }

    public static function get(
        string $microserviceName,
        string $uri,
        array $params = [],
        array $additionalHeaders = []
    ) {
        return self::send(
            method: 'get',
            microserviceName: $microserviceName,
            uri: $uri,
            data: $params,
            additionalHeaders: $additionalHeaders
        );
    }

    public static function post(
        string $microserviceName,
        string $uri,
        array $data = [],
        array $additionalHeaders = []
    ) {
        return self::send(
            method: 'post',
            microserviceName: $microserviceName,
            uri: $uri,
            data: $data,
            additionalHeaders: $additionalHeaders
        );
    }

    public static function put(
        string $microserviceName,
        string $uri,
        array $data = [],
        array $additionalHeaders = []
    ) {
        return self::send(
            method: 'put',
            microserviceName: $microserviceName,
            uri: $uri,
            data: $data,
            additionalHeaders: $additionalHeaders
        );
    }

    // ! need to revision as it has no request body to encode or decode
    // public static function delete(string $microserviceName, string $uri)
    // {
    //     return self::send('delete', $microserviceName, $uri);
    // }

    public static function establish(string $microserviceName, string $origin, int|null $localPort = null)
    {
        if (self::firstMsBy('name', $microserviceName)) abort(503, 'service name already exists');
        if (config('microservice.micro_service_name') == '') abort(503, 'current service name is not set yet in "microservice" config file');
        if (config('microservice.project_secret') == '') abort(503, 'project secret is not set yet in "microservice" config file');
        $data = [
            'name' => config('microservice.micro_service_name'),
            'origin' => self::origin(localPort: $localPort),
            'secret' => config('microservice.local_secret'),
        ];
        $response = self::send(
            method: 'post',
            microserviceName: $microserviceName,
            uri: 'micro-services',
            data: $data,
            origin: $origin
        );

        if (isset(((object) $response->json())->secret)) {
            self::saveMicroservice($microserviceName, $origin, ((object) $response->json())->secret);
        }
        return $response;
    }

    protected static function send(
        string $method,
        string $microserviceName,
        string $uri,
        array $data = [],
        string $origin = '',
        array $additionalHeaders = []
    ) {
        $http = new self($microserviceName, $origin);
        $establish = $origin != '';
        return Http::withHeaders($http->headers($method, $additionalHeaders))
            ->$method(
                "{$http->protocol}{$http->microservice->origin}/api/{$uri}",
                $http->encodeRequestBody($data, $establish)
            );
    }

    protected static function firstMsBy(string $columnName, string|int $value)
    {
        return self::cache()?->where($columnName, $value)?->first() ?? self::cache(true)?->where($columnName, $value)?->first();
    }

    public static function runOnConnection(Closure $closure, string $connectionName = '') {
        $currentDatabaseConnection = MsHttp::currentDBConnection();
        if (\in_array($connectionName, ['', config('database.default')])) return $closure();

        MsHttp::setDBConnection($connectionName);
        $resultOfClosure = $closure();
        MsHttp::setDBConnection($currentDatabaseConnection);
        return $resultOfClosure;
    }

    public static function saveMicroservice(string $microserviceName, string $origin, string $destinationKey)
    {
        $microService = self::runOnConnection(function () use ($microserviceName, $origin, $destinationKey) {
            return MicroServiceMap::updateOrCreate(
                [
                    'origin' => $origin,
                    'name' => $microserviceName,
                ],
                [
                    'display_name' => \ucfirst(\str_replace(['_', '-'], ' ', $microserviceName)),
                    'destination_key' => $destinationKey,
                ]
            );
        }, config('microservice.db_connection_name'));
        self::cache(true);
        return $microService;
    }

    protected function headers(string $method = '', array $additionalHeaders = [])
    {
        $headers = [
            'origin' => self::origin(),
            'Accept' => 'application/json',
            'Authorization' => 'application/json',
        ];
        if ($method == 'put') $headers['_method'] = 'put';
        return \array_merge($headers, $additionalHeaders);
    }

    protected static function origin(bool $inbound = false, int|null $localPort = null)
    {
        $origin = $inbound ? request()->header('origin') : request()->getSchemeAndHttpHost();
        if ($localPort && \in_array($origin, ['localhost', '127.0.0.1'])) {
            $origin = "{$origin}:{$localPort}";
        }

        return \str_replace(['http://', 'https://'], '', $origin);
    }

    protected static function cache(bool $force = false)
    {
        try {
            if ($force) {
                self::forgetCache();
                Cache::rememberForever('micro-services', function () {
                    return self::runOnConnection(function () {
                        return MicroServiceMap::get();
                    }, config('microservice.db_connection_name'));
                });
            }
            return Cache::has('micro-services') ? Cache::get('micro-services') : self::cache(true);
        } catch (Exception $exception) {
            abort(503, 'micro-service package is not installed yet or your configuration file is not configured yet. please run "php artisan vendor:publish" and "php artisan ms:install" commands and make sure that you set config file required values. Exception:' . $exception->getMessage());
        }
    }

    protected static function forgetCache() : void
    {
        Cache::forget('micro-services');
    }

    public static function refreshCache() : void
    {
        self::cache(true);
    }

    public static function announceSecretChange(string $secret): void
    {
        self::cache()->each(function ($microservice) use ($secret) {
            self::post(
                microserviceName: $microservice->name,
                uri: 'micro-services',
                data: [
                    'name' => config('microservice.micro_service_name'),
                    'origin' => self::origin(),
                    'secret' => $secret,
                ],
            );
        });
        append_to_env_content(envKey:'MS_LOCAL_SECRET', envKeyValue: $secret);
        Artisan::call('config:cache');
    }

    public static function decodeRequest(bool $establish = false)
    {
        return self::decodeRequestBody($establish);
    }

    protected function encodeRequestBody(array $data, bool $establish = false)
    {
        $payload = \base64_encode(\json_encode($data));
        return [
            0 => \openssl_encrypt(
                \serialize($payload),
                $this->cipher,
                '',
                0,
                $establish ? config('microservice.project_secret') : $this->microservice->destination_key
            ),
        ];
    }

    protected static function decodeRequestBody(bool $establish = false)
    {
        try {
            request_passed_ssl_configuration();
            $originMs = self::firstMsBy('origin', self::origin(true));
            if ((! $establish) && (! $originMs)) return false;

            $decrypted = \openssl_decrypt(
                request()->all()[0],
                'aes-256-cbc',
                '',
                0,
                $establish ? config('microservice.project_secret') : config('microservice.local_secret')
            );
            $unSerialized = \unserialize($decrypted);
            request()->merge((array) \json_decode(\base64_decode($unSerialized), true));
            request()->isMethod('get') ? request()->query->remove('0') : request()->request->remove('0');
            if (! $establish) self::setCurrentRequestOriginMs($originMs);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    protected static function currentDBConnection()
    {
        return DB::connection()->getPdo()?->getAttribute(\PDO::ATTR_DRIVER_NAME) ?? config('database.default');
    }

    protected static function setCurrentRequestOriginMs(MicroServiceMap $originMs)
    {
        request()->currentRequestMs = $originMs;
    }

    protected static function setDBConnection(string|null $dbConnectionName = '')
    {
        switch (true) {
            case $dbConnectionName != '' :
                break;
            case config('microservice.db_connection_name') != '' :
                $dbConnectionName = config('microservice.db_connection_name');
                break;
            case config('database.default') != '' :
                $dbConnectionName = config('database.default');
                break;
            default :
                abort(503, 'wrong database connaction name');
        }
        DB::purge();
        DB::setDefaultConnection($dbConnectionName);
    }

    public static function current()
    {
        return request()->currentRequestMs ?? self::testingMicroservice();
    }

    public static function testingMicroservice()
    {
        request()->currentRequestMs = App::environment('production') ? null : MicroServiceMap::first() ?? MicroServiceMap::factory()->create();
        return request()->currentRequestMs;
    }
}
