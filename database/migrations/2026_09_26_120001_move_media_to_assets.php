<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Re-homes media that was uploaded straight onto products and pages: each file
 * becomes an Asset, and the original owner references it instead. The media rows
 * keep their ids, so files and conversions stay where they are on disk.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('media')
                ->whereIn('collection_name', ['featured', 'gallery'])
                ->where('model_type', '!=', 'App\\Models\\Asset')
                ->orderBy('id')
                ->get()
                ->each(function (object $media): void {
                    $now = now();

                    $assetId = DB::table('assets')->insertGetId([
                        'name' => $media->name,
                        'alt' => $media->alt,
                        'created_at' => $media->created_at ?? $now,
                        'updated_at' => $now,
                    ]);

                    $ownerTable = (new $media->model_type)->getTable();

                    if ($media->collection_name === 'featured') {
                        DB::table($ownerTable)
                            ->where('id', $media->model_id)
                            ->update(['featured_asset_id' => $assetId]);
                    } else {
                        DB::table('asset_gallery')->insert([
                            'asset_id' => $assetId,
                            'model_type' => $media->model_type,
                            'model_id' => $media->model_id,
                            'order_column' => $media->order_column ?? 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    DB::table('media')->where('id', $media->id)->update([
                        'model_type' => 'App\\Models\\Asset',
                        'model_id' => $assetId,
                        'collection_name' => 'image',
                    ]);
                });
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('alt');
        });
    }
};
