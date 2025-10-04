<?php
namespace AIPM\C2PA;

use AIPM\Support\Options;

/**
 * Builds C2PA-Light JSON manifest for uploaded media.
 */
class ManifestBuilder
{
    /**
     * Create and save manifest for an attachment.
     *
     * @param int $attachment_id WordPress attachment ID.
     * @return bool True on success, false on failure.
     */
    public static function create_manifest(int $attachment_id): bool
    {
        // Check if C2PA is enabled
        $options = Options::get();
        if (empty($options['c2pa']['enabled'])) {
            return false;
        }

        // Get attachment data
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }

        // Only process images for MVP
        if (strpos($attachment->post_mime_type, 'image/') !== 0) {
            return false;
        }

        // Build manifest data
        $manifest = self::build_manifest_data($attachment_id, $attachment);

        // Save manifest to file
        return self::save_manifest($attachment_id, $manifest);
    }

    /**
     * Build manifest data array.
     *
     * @param int      $attachment_id Attachment ID.
     * @param \WP_Post $attachment    Attachment post object.
     * @return array Manifest data.
     */
    private static function build_manifest_data(int $attachment_id, \WP_Post $attachment): array
    {
        $options = Options::get();
        $ai_assisted = $options['c2pa']['ai_assisted_default'] ?? false;

        // Get attachment URL
        $url = wp_get_attachment_url($attachment_id);

        // Get file metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        $manifest = [
            'version' => '1.0',
            'type' => 'c2pa-light',
            'generated_at' => current_time('c'),
            'attachment_id' => $attachment_id,
            'creator' => get_bloginfo('name'),
            'created_at' => get_post_time('c', false, $attachment),
            'ai_assisted' => $ai_assisted,
            'source' => $url,
            'mime_type' => $attachment->post_mime_type,
        ];

        // Add optional metadata
        if (!empty($metadata['width'])) {
            $manifest['width'] = $metadata['width'];
        }
        if (!empty($metadata['height'])) {
            $manifest['height'] = $metadata['height'];
        }
        if (!empty($metadata['file'])) {
            $manifest['file'] = basename($metadata['file']);
        }

        // Add uploader info
        $uploader = get_userdata($attachment->post_author);
        if ($uploader) {
            $manifest['uploaded_by'] = $uploader->display_name;
        }

        return $manifest;
    }

    /**
     * Save manifest to JSON file.
     *
     * @param int   $attachment_id Attachment ID.
     * @param array $manifest      Manifest data.
     * @return bool True on success, false on failure.
     */
    private static function save_manifest(int $attachment_id, array $manifest): bool
    {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        
        // Create gatekeeper-ai directory
        $gkai_dir = $base_dir . '/gatekeeper-ai';
        if (!file_exists($gkai_dir)) {
            if (!wp_mkdir_p($gkai_dir)) {
                error_log('Gatekeeper AI: Failed to create manifest directory: ' . $gkai_dir);
                return false;
            }
        }

        // Create manifest file path
        $manifest_file = $gkai_dir . '/' . $attachment_id . '.json';

        // Encode manifest as JSON
        $json = wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            error_log('Gatekeeper AI: Failed to encode manifest for attachment ' . $attachment_id);
            return false;
        }

        // Save to file
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        $result = file_put_contents($manifest_file, $json);
        if ($result === false) {
            error_log('Gatekeeper AI: Failed to write manifest file: ' . $manifest_file);
            return false;
        }

        // Store manifest path in post meta for easy retrieval
        update_post_meta($attachment_id, '_gkai_c2pa_manifest', $manifest_file);

        return true;
    }

    /**
     * Get manifest for an attachment.
     *
     * @param int $attachment_id Attachment ID.
     * @return array|null Manifest data or null if not found.
     */
    public static function get_manifest(int $attachment_id): ?array
    {
        $manifest_file = get_post_meta($attachment_id, '_gkai_c2pa_manifest', true);
        
        if (empty($manifest_file) || !file_exists($manifest_file)) {
            return null;
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $json = file_get_contents($manifest_file);
        if ($json === false) {
            return null;
        }

        $manifest = json_decode($json, true);
        return is_array($manifest) ? $manifest : null;
    }
}
