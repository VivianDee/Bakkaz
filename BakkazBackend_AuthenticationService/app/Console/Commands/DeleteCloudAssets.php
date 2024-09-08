<?php

namespace App\Console\Commands;

use App\Helpers\AssetHelpers;
use App\Impl\CloudinaryImpl;
use App\Models\Asset;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteCloudAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-cloud-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete cloud assets that are not verification assets and are at least 1 day old';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch all grouped assets that are not of type 'verification-asset' and are older than 1 day
        $assets = Asset::where('asset_type', 'recenth-posts-service/post-asset')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        // Check if there are any grouped assets to process
        if ($assets->isEmpty()) {
            $this->info('No assets to delete.');
            return;
        }
        foreach ($assets as $asset) {
                       // Extract the public ID from the asset path
                       $extractedPublicId = AssetHelpers::extractPubliicId($asset->path);

                       $cloudinaryPublicId = "{$asset->asset_type}/{$extractedPublicId}";

                       $this->info("Attempting to delete Cloudinary asset with public ID: {$cloudinaryPublicId}");

                       // Attempt to delete the asset from Cloudinary
                       $cloudDeleted = CloudinaryImpl::hardDeleteImage($cloudinaryPublicId);

                       // Check the result of the deletion
                       if ($cloudDeleted) {
                           $this->info("Successfully deleted asset from Cloudinary: {$cloudinaryPublicId}");
                           // Delete the asset from the database
                           // $asset->delete();
                           $this->info("Successfully deleted asset from database: {$cloudinaryPublicId}");
                       } else {
                           $this->error("Error deleting asset from Cloudinary: {$cloudinaryPublicId}");
                       }
                   }
        $this->info('Cloud asset deletion process completed.');
    }
}
