<?php
namespace AIPM\Admin\MetaBoxes;

use AIPM\Support\Capabilities;
use AIPM\Policies\BotDirectory;

/**
 * Post policy metabox for per-post AI bot access control.
 */
class PostPolicyMetaBox
{
    /**
     * Register metabox for public post types.
     *
     * @return void
     */
    public static function register(): void
    {
        $post_types = get_post_types(['public' => true], 'names');
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'gkai_policy',
                __('Gatekeeper AI Policy', 'gatekeeper-ai'),
                [self::class, 'render'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the metabox content.
     *
     * @param \WP_Post $post Current post object.
     * @return void
     */
    public static function render($post): void
    {
        if (!Capabilities::can_manage()) {
            return;
        }

        // Get saved values
        $policy = get_post_meta($post->ID, '_gkai_policy', true) ?: 'default';
        $bots = get_post_meta($post->ID, '_gkai_policy_bots', true) ?: ['allow' => [], 'block' => []];
        
        if (!is_array($bots)) {
            $bots = ['allow' => [], 'block' => []];
        }
        if (!isset($bots['allow'])) {
            $bots['allow'] = [];
        }
        if (!isset($bots['block'])) {
            $bots['block'] = [];
        }

        // Security nonce
        wp_nonce_field('gkai_policy_meta', '_gkai_nonce');

        echo '<div class="gkai-post-policy">';
        
        // General policy dropdown
        echo '<p>';
        echo '<label for="gkai_policy"><strong>' . esc_html__('General Policy', 'gatekeeper-ai') . '</strong></label>';
        echo '</p>';
        echo '<select id="gkai_policy" name="gkai_policy" style="width: 100%;">';
        
        foreach (['default', 'allow', 'block'] as $opt) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($opt),
                selected($policy, $opt, false),
                esc_html(ucfirst($opt))
            );
        }
        
        echo '</select>';
        
        echo '<p class="description">';
        echo esc_html__('Default: Use global settings. Allow: Allow all bots. Block: Block all bots.', 'gatekeeper-ai');
        echo '</p>';

        // Bot-specific overrides
        $available_bots = BotDirectory::list();
        
        if (!empty($available_bots)) {
            echo '<hr style="margin: 15px 0;">';
            echo '<p><strong>' . esc_html__('Bot-Specific Overrides', 'gatekeeper-ai') . '</strong></p>';
            echo '<p class="description">' . esc_html__('Override policy for specific bots (takes precedence over general policy).', 'gatekeeper-ai') . '</p>';
            
            echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; background: #f9f9f9; margin-top: 8px;">';
            
            foreach ($available_bots as $bot) {
                $bot_name = $bot['name'];
                $is_allowed = in_array($bot_name, $bots['allow'], true);
                $is_blocked = in_array($bot_name, $bots['block'], true);
                
                echo '<div style="margin-bottom: 10px; padding: 5px; background: #fff; border: 1px solid #e0e0e0;">';
                echo '<div style="font-weight: 600; margin-bottom: 4px;">' . esc_html($bot_name) . '</div>';
                echo '<label style="display: inline-block; margin-right: 15px;">';
                echo '<input type="radio" name="gkai_bot_' . esc_attr($bot_name) . '" value="" ' . checked($is_allowed || $is_blocked, false, false) . '> ';
                echo esc_html__('Default', 'gatekeeper-ai');
                echo '</label>';
                echo '<label style="display: inline-block; margin-right: 15px;">';
                echo '<input type="radio" name="gkai_bot_' . esc_attr($bot_name) . '" value="allow" ' . checked($is_allowed, true, false) . '> ';
                echo esc_html__('Allow', 'gatekeeper-ai');
                echo '</label>';
                echo '<label style="display: inline-block;">';
                echo '<input type="radio" name="gkai_bot_' . esc_attr($bot_name) . '" value="block" ' . checked($is_blocked, true, false) . '> ';
                echo esc_html__('Block', 'gatekeeper-ai');
                echo '</label>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Save metabox data.
     *
     * @param int $post_id Post ID.
     * @return void
     */
    public static function save($post_id): void
    {
        // Verify nonce
        if (!isset($_POST['_gkai_nonce']) || !wp_verify_nonce($_POST['_gkai_nonce'], 'gkai_policy_meta')) {
            return;
        }

        // Check permissions
        if (!Capabilities::can_manage()) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Save general policy
        $policy = isset($_POST['gkai_policy']) ? sanitize_text_field($_POST['gkai_policy']) : 'default';
        if (!in_array($policy, ['default', 'allow', 'block'], true)) {
            $policy = 'default';
        }
        update_post_meta($post_id, '_gkai_policy', $policy);

        // Save bot-specific overrides
        $bots = ['allow' => [], 'block' => []];
        $available_bots = BotDirectory::list();
        $valid_bot_names = array_column($available_bots, 'name');

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'gkai_bot_') === 0) {
                $bot_name = substr($key, 9); // Remove 'gkai_bot_' prefix
                
                // Validate bot name
                if (!in_array($bot_name, $valid_bot_names, true)) {
                    continue;
                }

                $value = sanitize_text_field($value);
                
                if ($value === 'allow') {
                    $bots['allow'][] = $bot_name;
                } elseif ($value === 'block') {
                    $bots['block'][] = $bot_name;
                }
            }
        }

        update_post_meta($post_id, '_gkai_policy_bots', $bots);
    }
}
