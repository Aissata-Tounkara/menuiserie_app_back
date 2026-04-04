<?php

namespace App\Services;

class DeviceDetector
{
    protected string $userAgent;

    public function __construct($request)
    {
        // Récupère le User-Agent depuis la requête
        $this->userAgent = strtolower($request->userAgent() ?? '');
    }

    // Retourne le nom de l'appareil
    public function getDeviceName(): string
    {
        if (str_contains($this->userAgent, 'iphone')) return 'iPhone';
        if (str_contains($this->userAgent, 'samsung')) return 'Samsung';
        if (str_contains($this->userAgent, 'android')) return 'Android';
        if (str_contains($this->userAgent, 'windows')) return 'Windows PC';
        if (str_contains($this->userAgent, 'mac')) return 'Mac';
        if (str_contains($this->userAgent, 'linux')) return 'Linux PC';

        return 'Appareil inconnu';
    }

    // Retourne le type : Mobile / Tablette / Desktop
    public function getDeviceType(): string
    {
        if (str_contains($this->userAgent, 'mobile')) return 'Mobile';
        if (str_contains($this->userAgent, 'tablet')) return 'Tablette';

        return 'Desktop';
    }

    // Retourne le navigateur utilisé
    // public function getBrowser(): string
    
    // {
    //     if (str_contains($this->userAgent, 'chrome')) return 'Chrome';
    //     if (str_contains($this->userAgent, 'firefox')) return 'Firefox';
    //     if (str_contains($this->userAgent, 'safari') && !str_contains($this->userAgent, 'chrome')) return 'Safari';
    //     if (str_contains($this->userAgent, 'edge')) return 'Edge';
    //     if (str_contains($this->userAgent, 'opera')) return 'Opera';

    //     return 'Navigateur inconnu';
    // }
}