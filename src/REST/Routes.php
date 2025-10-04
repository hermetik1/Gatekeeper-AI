<?php
namespace AIPM\REST;

use AIPM\Support\Options;
use AIPM\Support\Capabilities;

/**
 * REST API route registration.
 */
class Routes
{
    /**
     * Register REST API routes.
     *
     * @return void
     */
    public static function register(): void
    {
        // Settings endpoint
        register_rest_route('aipm/v1', '/settings', [
            [
                'methods' => 'GET',
                'permission_callback' => [self::class, 'can_manage'],
                'callback' => function () {
                    return Options::get();
                }
            ],
            [
                'methods' => 'POST',
                'permission_callback' => [self::class, 'can_manage'],
                'callback' => function ($request) {
                    $data = $request->get_json_params() ?: [];
                    
                    if (!is_array($data)) {
                        return new \WP_Error('invalid_data', 'Invalid data format', ['status' => 400]);
                    }

                    // Whitelist allowed top-level keys
                    $allowed_keys = ['policies', 'c2pa', 'logging', 'debug'];
                    $filtered_data = array_intersect_key($data, array_flip($allowed_keys));

                    // Validate policies if present
                    if (isset($filtered_data['policies'])) {
                        $validation = \AIPM\Policies\PolicyManager::validate($filtered_data['policies']);
                        if (!$validation['valid']) {
                            return new \WP_Error('validation_failed', implode('; ', $validation['errors']), ['status' => 400]);
                        }
                    }
                    
                    Options::update($filtered_data);
                    return Options::get();
                }
            ]
        ]);

        // Bots directory endpoint
        register_rest_route('aipm/v1', '/bots', [
            'methods' => 'GET',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function () {
                return \AIPM\Policies\BotDirectory::list();
            }
        ]);

        // Preview robots.txt endpoint
        register_rest_route('aipm/v1', '/preview-robots', [
            'methods' => 'GET',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function () {
                ob_start();
                \AIPM\Policies\RobotsTxtGenerator::output();
                $content = ob_get_clean();
                return ['content' => $content];
            }
        ]);

        // Logs endpoint
        register_rest_route('aipm/v1', '/logs', [
            'methods' => 'GET',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function ($request) {
                $filters = [
                    'bot' => $request->get_param('bot'),
                    'since' => $request->get_param('since'),
                    'until' => $request->get_param('until'),
                    'result' => $request->get_param('result'),
                    'limit' => $request->get_param('limit') ?? 200,
                ];

                // Remove empty filters
                $filters = array_filter($filters, function ($v) {
                    return $v !== null && $v !== '';
                });

                return \AIPM\Logging\AccessLogger::get_logs($filters);
            }
        ]);

        // Logs stats endpoint
        register_rest_route('aipm/v1', '/logs/stats', [
            'methods' => 'GET',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function ($request) {
                $days = $request->get_param('days') ?? 7;
                return \AIPM\Logging\AccessLogger::get_stats((int) $days);
            }
        ]);

        // Clear logs endpoint
        register_rest_route('aipm/v1', '/logs/clear', [
            'methods' => 'POST',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function () {
                $success = \AIPM\Logging\AccessLogger::clear_logs();
                return ['success' => $success];
            }
        ]);

        // Test merge endpoint
        register_rest_route('aipm/v1', '/tools/test-merge', [
            'methods' => 'POST',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function ($request) {
                $params = $request->get_json_params() ?: [];
                $path = $params['path'] ?? '/';
                $post_id = $params['post_id'] ?? null;

                $merged = \AIPM\Policies\PolicyManager::merged_for_path($path, $post_id);
                
                return [
                    'path' => $path,
                    'post_id' => $post_id,
                    'merged_policy' => $merged,
                ];
            }
        ]);

        // Export policies endpoint
        register_rest_route('aipm/v1', '/tools/export', [
            'methods' => 'GET',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function () {
                $policies = \AIPM\Policies\PolicyManager::get();
                return [
                    'version' => '1.0',
                    'exported_at' => current_time('c'),
                    'policies' => $policies,
                ];
            }
        ]);

        // Import policies endpoint
        register_rest_route('aipm/v1', '/tools/import', [
            'methods' => 'POST',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function ($request) {
                $data = $request->get_json_params() ?: [];
                
                if (!isset($data['policies'])) {
                    return new \WP_Error('invalid_import', 'No policies found in import data', ['status' => 400]);
                }

                $policies = $data['policies'];
                
                // Validate imported policies
                $validation = \AIPM\Policies\PolicyManager::validate($policies);
                if (!$validation['valid']) {
                    return new \WP_Error('validation_failed', implode('; ', $validation['errors']), ['status' => 400]);
                }

                // Update policies
                $options = Options::get();
                $options['policies'] = $policies;
                Options::update($options);

                return [
                    'success' => true,
                    'message' => 'Policies imported successfully',
                ];
            }
        ]);

        // C2PA scan endpoint
        register_rest_route('aipm/v1', '/c2pa/scan', [
            'methods' => 'GET',
            'permission_callback' => [self::class, 'can_manage'],
            'callback' => function ($request) {
                $limit = $request->get_param('limit') ?? 20;
                
                $attachments = get_posts([
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'posts_per_page' => (int) $limit,
                    'orderby' => 'rand',
                    'post_status' => 'inherit',
                ]);

                $results = [];
                $upload_dir = wp_upload_dir();
                $gkai_dir = $upload_dir['basedir'] . '/gatekeeper-ai';

                foreach ($attachments as $attachment) {
                    $manifest_file = $gkai_dir . '/' . $attachment->ID . '.json';
                    $has_manifest = file_exists($manifest_file);
                    
                    $results[] = [
                        'id' => $attachment->ID,
                        'title' => $attachment->post_title,
                        'url' => wp_get_attachment_url($attachment->ID),
                        'has_manifest' => $has_manifest,
                        'uploaded' => $attachment->post_date,
                    ];
                }

                return [
                    'attachments' => $results,
                    'total_scanned' => count($results),
                ];
            }
        ]);
    }

    /**
     * Check if current user can manage plugin settings.
     *
     * @return bool True if user can manage.
     */
    public static function can_manage(): bool
    {
        return Capabilities::can_manage() && is_user_logged_in();
    }
}
