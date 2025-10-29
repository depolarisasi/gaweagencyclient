<?php

namespace App\Http\Controllers;

use App\Services\DomainService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    protected $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    /**
     * Check domain availability
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'domain' => 'required|string|max:255'
        ]);

        $domain = strtolower(trim($request->domain));

        // Validate domain format
        if (!$this->domainService->validateDomain($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Format domain tidak valid'
            ], 400);
        }

        $result = $this->domainService->checkAvailability($domain);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get domain suggestions
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'domain' => 'required|string|max:255'
        ]);

        $domain = strtolower(trim($request->domain));
        $suggestions = $this->domainService->getDomainSuggestions($domain);

        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions
            ]
        ]);
    }

    /**
     * Get supported TLDs
     */
    public function getSupportedTlds(): JsonResponse
    {
        $tlds = $this->domainService->getSupportedTlds();

        return response()->json([
            'success' => true,
            'data' => [
                'tlds' => $tlds
            ]
        ]);
    }

    // Harga domain tidak digunakan lagi karena sudah termasuk dalam subscription.
}