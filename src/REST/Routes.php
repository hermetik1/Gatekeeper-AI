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
                        $data = [];
                    }
                    
                    Options::update($data);
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
