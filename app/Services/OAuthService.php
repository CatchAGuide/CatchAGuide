<?php

namespace App\Services;

use App\Models\OAuthToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

abstract class OAuthService
{
    protected $providerType;
    protected $config;
    
    public function __construct($providerType, $config = [])
    {
        $this->providerType = $providerType;
        $this->config = $config;
    }
    
    /**
     * Get OAuth authorization URL
     */
    abstract public function getAuthorizationUrl($state = null);
    
    /**
     * Exchange authorization code for access token
     */
    abstract public function exchangeCodeForToken($code);
    
    /**
     * Refresh access token
     */
    abstract public function refreshToken($refreshToken);
    
    /**
     * Store or update OAuth token for user
     */
    public function storeToken($userId, $tokenData, $providerData = [])
    {
        try {
            $expiresAt = null;
            if (isset($tokenData['expires_in'])) {
                $expiresAt = Carbon::now()->addSeconds($tokenData['expires_in']);
            }
            
            $token = OAuthToken::updateOrCreate(
                [
                    'user_id' => $userId,
                    'type' => $this->providerType,
                ],
                [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'token_type' => $tokenData['token_type'] ?? 'Bearer',
                    'expires_at' => $expiresAt,
                    'provider_data' => $providerData,
                    'status' => 'active',
                ]
            );
            
            Log::info("OAuth token stored for user {$userId} and provider {$this->providerType}");
            return $token;
            
        } catch (\Exception $e) {
            Log::error("Failed to store OAuth token", [
                'user_id' => $userId,
                'provider' => $this->providerType,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Revoke OAuth token
     */
    public function revokeToken($userId)
    {
        try {
            $token = OAuthToken::where('user_id', $userId)
                              ->where('type', $this->providerType)
                              ->first();
            
            if ($token) {
                $token->update(['status' => 'revoked']);
                Log::info("OAuth token revoked for user {$userId} and provider {$this->providerType}");
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to revoke OAuth token", [
                'user_id' => $userId,
                'provider' => $this->providerType,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get active token for user
     */
    public function getActiveToken($userId)
    {
        return OAuthToken::getTokenForUser($userId, $this->providerType);
    }
    
    /**
     * Check if user has active connection
     */
    public function hasActiveConnection($userId)
    {
        $token = $this->getActiveToken($userId);
        return $token && $token->isActive();
    }
    
    /**
     * Refresh token if expired
     */
    public function refreshTokenIfNeeded($userId)
    {
        $token = $this->getActiveToken($userId);
        
        if (!$token || !$token->isExpired()) {
            return $token;
        }
        
        if (!$token->refresh_token) {
            Log::warning("No refresh token available for user {$userId} and provider {$this->providerType}");
            return null;
        }
        
        $newTokenData = $this->refreshToken($token->refresh_token);
        if (!$newTokenData) {
            Log::error("Failed to refresh token for user {$userId} and provider {$this->providerType}");
            return null;
        }
        
        return $this->storeToken($userId, $newTokenData, $token->provider_data);
    }
} 