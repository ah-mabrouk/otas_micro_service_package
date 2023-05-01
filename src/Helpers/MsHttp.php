<?php

namespace Solutionplus\MicroService\Helpers;

use Exception;
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
        if (self::cache()->where('name', $microserviceName)->first()) abort(503, 'service name already exists');
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
        $microService = self::cache()?->where('name', $microserviceName)?->first();
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

    protected static function cache(bool $force = false)
    {
        try {
            if ($force) {
                self::forgetCache();
                Cache::rememberForever('micro-services', function () {
                    return MicroServiceMap::connection(config('microservice.db_connection_name') ?? config('database.default'))->get();
                });
            }
            return Cache::has('micro-services') ? Cache::get('micro-services') : self::cache(true);
        } catch (Exception $exception) {
            abort(503, 'micro-service package is not installed yet. please run "php artisan ms:install" command');
        }
    }

    protected static function forgetCache() : void
    {
        Cache::forget('micro-services');
    }

    protected static function send(string $method, string $microserviceName, string $uri, array $data = [], string $origin = '')
    {
        $http = new self($microserviceName, $origin);
        $establish = $origin != '';
        $response = Http::withHeaders($http->headers($method))
            ->$method("{$http->protocol}{$http->microservice->origin}/api/{$uri}", $http->encodeRequestBody($data, $establish));
        $response = $response->json();
        return (object) $response;
    }

    public static function addNewMicroService(string $microserviceName, string $origin, string $destinationKey)
    {
        $microService = MicroServiceMap::connaction(config('microservice.db_connection_name') ?? config('database.default'))->create([
            'name' => $microserviceName,
            'display_name' => \ucfirst(\str_replace(['_', '-'], ' ', $microserviceName)),
            'origin' => $origin,
            'destination_key' => $destinationKey,
        ]);
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

    public static function decodeRequest(bool $establish = false)
    {
        return self::decodeRequestBody($establish);
    }

    protected static function decodeRequestBody(bool $establish = false)
    {
        try {
            request_passed_ssl_configuration();

            if ((! $establish) && (! self::cache()->where('origin', self::origin(true))?->first())) return false;
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
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
