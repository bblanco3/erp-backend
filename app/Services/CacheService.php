<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheService
{
    private $jsonStoragePath;
    private $cachePrefix;
    private $cacheDuration;

    public function __construct()
    {
        $this->jsonStoragePath = storage_path('app/json');
        $this->cachePrefix = 'synergy_';
        $this->cacheDuration = 3600; // 1 hour default

        if (!File::exists($this->jsonStoragePath)) {
            File::makeDirectory($this->jsonStoragePath, 0755, true);
        }
    }

    public function storeUserData(array $userData)
    {
        $userId = $userData['id'];
        $jsonFile = $this->jsonStoragePath . "/user_{$userId}.json";
        
        // Store in JSON file
        File::put($jsonFile, json_encode($userData, JSON_PRETTY_PRINT));
        
        // Store in cache
        Cache::put(
            $this->cachePrefix . "user_{$userId}", 
            $userData, 
            now()->addSeconds($this->cacheDuration)
        );

        return $userData;
    }

    public function getUserData($userId)
    {
        // Try cache first
        $cachedData = Cache::get($this->cachePrefix . "user_{$userId}");
        if ($cachedData) {
            return $cachedData;
        }

        // If not in cache, try JSON file
        $jsonFile = $this->jsonStoragePath . "/user_{$userId}.json";
        if (File::exists($jsonFile)) {
            $userData = json_decode(File::get($jsonFile), true);
            
            // Refresh cache
            Cache::put(
                $this->cachePrefix . "user_{$userId}", 
                $userData, 
                now()->addSeconds($this->cacheDuration)
            );
            
            return $userData;
        }

        return null;
    }

    public function clearUserData($userId)
    {
        // Clear cache
        Cache::forget($this->cachePrefix . "user_{$userId}");
        
        // Clear JSON file
        $jsonFile = $this->jsonStoragePath . "/user_{$userId}.json";
        if (File::exists($jsonFile)) {
            File::delete($jsonFile);
        }
    }

    public function updateUserData($userId, array $newData)
    {
        $existingData = $this->getUserData($userId);
        if ($existingData) {
            $updatedData = array_merge($existingData, $newData);
            return $this->storeUserData($updatedData);
        }
        return false;
    }

    public function isUserActive($userId)
    {
        $userData = $this->getUserData($userId);
        return $userData && isset($userData['active']) && $userData['active'];
    }
}
