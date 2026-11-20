<?php
/**
 * Plugin Name: Copperline Quotes
 * Description: Validates Copperline quote requests and stores submissions in the local WordPress database.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

final class Copperline_Quotes
{
    private const SERVICES = [
        'leak' => 'Leak or pipe repair',
        'drain' => 'Drain clearing',
        'heater' => 'Water heater service',
        'fixture' => 'Fixture installation',
        'other' => 'Something else',
    ];
    private const FIELDS = [
        'name' => 'Name',
        'email' => 'Email',
        'zip' => 'ZIP code',
        'service' => 'Service',
        'details' => 'Project details',
    ];
    private static array $values = [];
    private static array $errors = [];

    public static function register(): void
    {
        register_post_type('copperline_quote', [
            'labels' => ['name' => 'Quote Requests', 'singular_name' => 'Quote Request'],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-clipboard',
            'supports' => ['title', 'editor'],
            'capability_type' => 'page',
            'map_meta_cap' => true,
            'capabilities' => ['create_posts' => 'do_not_allow'],
        ]);
    }

    private static function input(string $key): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
    }

    public static function submit(): void
    {
        if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') || !is_page('contact') || 'quote' !== self::input('cl_action')) {
            return;
        }
        nocache_headers();

        foreach (self::FIELDS as $key => $label) {
            $raw = self::input('cl_' . $key);
            self::$values[$key] = 'details' === $key ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
        }
        if (!wp_verify_nonce(self::input('cl_nonce'), 'copperline_quote')) {
            self::$errors['form'] = 'The form expired. Review your details and try again.';
            status_header(403);
            return;
        }
        if ('' !== self::input('cl_website')) {
            status_header(400);
            self::$errors['form'] = 'The request could not be saved. Try again.';
            return;
        }

        $values = self::$values;
        if ('' === $values['name'] || mb_strlen($values['name']) > 100) {
            self::$errors['name'] = 'Enter a name of up to 100 characters.';
        }
        if (!is_email($values['email']) || strlen($values['email']) > 254) {
            self::$errors['email'] = 'Enter a valid email address.';
        }
        if (!preg_match('/^[0-9]{5}$/D', $values['zip'])) {
            self::$errors['zip'] = 'Enter a five-digit ZIP code.';
        }
        if (!isset(self::SERVICES[$values['service']])) {
            self::$errors['service'] = 'Choose a service from the list.';
        }
        $length = mb_strlen($values['details']);
        if ($length < 15 || $length > 1500) {
            self::$errors['details'] = 'Describe the job in 15 to 1,500 characters.';
        }
        if (self::$errors) {
            status_header(422);
            return;
        }

        $post = wp_insert_post(wp_slash([
            'post_type' => 'copperline_quote',
            'post_status' => 'private',
            'post_title' => self::SERVICES[$values['service']] . ' · ' . $values['zip'],
            'post_content' => sprintf(
                "Name: %s\nEmail: %s\nZIP code: %s\nService: %s\n\n%s",
                $values['name'],
                $values['email'],
                $values['zip'],
                self::SERVICES[$values['service']],
                $values['details']
            ),
        ]), true);
        if (is_wp_error($post) || !$post) {
            self::$errors['form'] = 'The request could not be saved. Try again.';
            status_header(500);
            return;
        }
        wp_safe_redirect(add_query_arg('quote', 'received', get_permalink()) . '#quote', 303);
        exit;
    }

    public static function render(): string
    {
        $received = isset($_GET['quote']) && 'received' === $_GET['quote'] && !self::$errors;
        ob_start();
        ?>
        <form class="cl-form" id="quote" action="<?php echo esc_url(get_permalink() . '#quote'); ?>" method="post" aria-labelledby="cl-form-title">
            <h2 id="cl-form-title">Request a quote</h2>
            <p>Use sample contact details. Requests are stored on this WordPress installation.</p>
            <?php if ($received) : ?>
                <div class="cl-status" role="status"><strong>Quote request received.</strong> Your request was saved.</div>
            <?php elseif (self::$errors) : ?>
                <div class="cl-status cl-error" role="alert">
                    <?php if (isset(self::$errors['form'])) : ?>
                        <?php echo esc_html(self::$errors['form']); ?>
                    <?php else : ?>
                        <strong>Review the highlighted fields.</strong>
                        <ul>
                            <?php foreach (self::$errors as $key => $error) : ?>
                                <li><a href="#cl-<?php echo esc_attr($key); ?>"><?php echo esc_html($error); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php wp_nonce_field('copperline_quote', 'cl_nonce', false); ?>
            <input type="hidden" name="cl_action" value="quote">
            <div class="cl-trap" aria-hidden="true">
                <label for="cl-website">Leave this field empty</label>
                <input id="cl-website" name="cl_website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <?php foreach (self::FIELDS as $key => $label) :
                $id = 'cl-' . $key;
                $name = 'cl_' . $key;
                $value = self::$values[$key] ?? '';
                $error = self::$errors[$key] ?? '';
                $status = $error ? sprintf(' aria-invalid="true" aria-describedby="%s-error"', esc_attr($id)) : '';
                ?>
                <div class="cl-field">
                    <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                    <?php if ('service' === $key) : ?>
                        <select id="cl-service" name="cl_service" required<?php echo $status; ?>>
                            <option value="">Select a service</option>
                            <?php foreach (self::SERVICES as $choice => $description) : ?>
                                <option value="<?php echo esc_attr($choice); ?>" <?php selected($value, $choice); ?>><?php echo esc_html($description); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ('details' === $key) : ?>
                        <textarea id="cl-details" name="cl_details" required minlength="15" maxlength="1500"<?php echo $status; ?>><?php echo esc_textarea($value); ?></textarea>
                    <?php else :
                        $attributes = match ($key) {
                            'name' => 'type="text" autocomplete="name" maxlength="100"',
                            'email' => 'type="email" autocomplete="email" maxlength="254"',
                            'zip' => 'type="text" inputmode="numeric" autocomplete="postal-code" pattern="[0-9]{5}" maxlength="5"',
                        };
                        ?>
                        <input id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" required <?php echo $attributes . $status; ?>>
                    <?php endif; ?>
                    <?php if ($error) : ?>
                        <p id="<?php echo esc_attr($id . '-error'); ?>"><?php echo esc_html($error); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button class="cl-button" type="submit">Send quote request</button>
        </form>
        <?php
        return ob_get_clean();
    }
}

add_action('init', [Copperline_Quotes::class, 'register']);
add_action('template_redirect', [Copperline_Quotes::class, 'submit']);
add_shortcode('copperline_quote_form', [Copperline_Quotes::class, 'render']);
