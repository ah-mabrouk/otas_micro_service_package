<?php

namespace Solutionplus\MicroService\Helpers;

use Closure;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
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

    public static function get(string $microserviceName, string $uri, array $params = [])
    {
        return self::send('get', $microserviceName, $uri, $params);
    }

    public static function post(string $microserviceName, string $uri, array $data = [])
    {
        return self::send('post', $microserviceName, $uri, $data);
    }

    public static function put(string $microserviceName, string $uri, array $data = [])
    {
        return self::send('put', $microserviceName, $uri, $data);
    }

    // ! need to revision as it has no body to encode or decode
    // public static function delete(string $microserviceName, string $uri)
    // {
    //     return self::send('delete', $microserviceName, $uri);
    // }

    public static function establish(string $microserviceName, string $origin)
    {
        if (self::firstMsBy('name', $microserviceName)) abort(503, 'service name already exists');
        if (config('microservice.micro_service_name') == '') abort(503, 'current service name is not set yet in "microservice" config file');
        if (config('microservice.project_secret') == '') abort(503, 'project secret is not set yet in "microservice" config file');
        $data = [
            'name' => config('microservice.micro_service_name'),
            'origin' => self::origin(),
            'secret' => config('microservice.local_secret'),
        ];
        $response = self::send('post', $microserviceName, 'micro-services', $data, $origin);
        if (isset($response->secret)) {
            self::addNewMicroService($microserviceName, $origin, $response->secret);
        }
        return $response;
    }

    protected static function send(string $method, string $microserviceName, string $uri, array $data = [], string $origin = '')
    {
        $http = new self($microserviceName, $origin);
        $establish = $origin != '';
        return Http::withHeaders($http->headers($method))->$method(
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
        if ($connectionName == config('database.default')) return $closure();

        MsHttp::setDBConnection($connectionName);
        $resultOfClosure = $closure();
        MsHttp::setDBConnection($currentDatabaseConnection);
        return $resultOfClosure;
    }

    public static function addNewMicroService(string $microserviceName, string $origin, string $destinationKey)
    {
        $microService = self::runOnConnection(function () use ($microserviceName, $origin, $destinationKey) {
            return MicroServiceMap::create([
                'name' => $microserviceName,
                'display_name' => \ucfirst(\str_replace(['_', '-'], ' ', $microserviceName)),
                'origin' => $origin,
                'destination_key' => $destinationKey,
            ]);
        });
        self::cache(true);
        return $microService;
    }

    protected function headers(string $method = '')
    {
        $headers = [
            'origin' => self::origin(),
            'Accept' => 'application/json',
            'Authorization' => 'application/json',
        ];
        if ($method == 'put') $headers['_method'] = 'put';
        return $headers;
    }

    protected static function origin(bool $inbound = false)
    {
        $origin = $inbound ? request()->header('origin') : request()->getSchemeAndHttpHost();
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
                    });
                });
            }
            return Cache::has('micro-services') ? Cache::get('micro-services') : self::cache(true);
        } catch (Exception $exception) {
            abort(503, 'micro-service package is not installed yet or your configuration file is not configured yet. please run "php artisan vendor:publish" and "php artisan ms:install" commands and make sure that you set config file required values');
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

    // public static function announceSecretChange(string $secret)
    // {
    //     $microserviceMaps = self::cache()->each(function ($microservice) use ($secret) {
    //         self::post($microservice->name, 'secret-changes', []);
    //     });
    //     return self::decodeRequestBody($establish);
    // }

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
            $unserialized = \unserialize($decrypted);
            request()->merge((array) \json_decode(\base64_decode($unserialized)));
            request()->isMethod('get') ? request()->query->remove('0') : request()->request->remove('0');
            self::setCurrentRequestOriginMs($originMs);
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
        return request()->currentRequestMs ?? MicroServiceMap::testingMicroservice();
    }

    public static function testingMicroservice()
    {
        request()->currentRequestMs = App::environment('production') ? null : MicroServiceMap::first() ?? MicroServiceMap::factory()->create();
        return request()->currentRequestMs;
    }
}
