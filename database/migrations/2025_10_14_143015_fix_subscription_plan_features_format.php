<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix subscription plans where features are stored as JSON strings instead of proper arrays
        $plans = DB::table('subscription_plans')->get();
        
        foreach ($plans as $plan) {
            if (is_string($plan->features)) {
                // Try to decode the JSON string
                $features = json_decode($plan->features, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($features)) {
                    // If it's valid JSON, re-encode it properly
                    DB::table('subscription_plans')
                        ->where('id', $plan->id)
                        ->update(['features' => json_encode($features)]);
                } else {
                    // If it's not valid JSON, set it to empty array
                    DB::table('subscription_plans')
                        ->where('id', $plan->id)
                        ->update(['features' => json_encode([])]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it only fixes data format
    }
};
