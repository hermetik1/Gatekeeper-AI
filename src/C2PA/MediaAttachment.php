<?php
namespace AIPM\C2PA;

/**
 * Handles C2PA manifest creation on media upload.
 */
class MediaAttachment
{
    /**
     * Register hooks for media attachment handling.
     *
     * @return void
     */
    public static function register(): void
    {
        // Hook into attachment upload
        add_action('add_attachment', [self::class, 'on_upload'], 10, 1);
        
        // Also hook into metadata generation for better timing
        add_filter('wp_generate_attachment_metadata', [self::class, 'on_metadata_generated'], 10, 2);
    }

    /**
     * Handle attachment upload.
     *
     * @param int $attachment_id Attachment ID.
     * @return void
     */
    public static function on_upload(int $attachment_id): void
    {
        ManifestBuilder::create_manifest($attachment_id);
    }

    /**
     * Handle metadata generation (better timing for complete data).
     *
     * @param array $metadata      Attachment metadata.
     * @param int   $attachment_id Attachment ID.
     * @return array Unchanged metadata.
     */
    public static function on_metadata_generated(array $metadata, int $attachment_id): array
    {
        // Create or update manifest after metadata is generated
        ManifestBuilder::create_manifest($attachment_id);
        
        return $metadata;
    }
}
