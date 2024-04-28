<h1>
    <?php esc_html_e('Import CSV', PLUGIN_DOMAIN); ?>
</h1>
<p><?php esc_html_e('Import a CSV of sowing calendar dates here.', PLUGIN_DOMAIN); ?></p>

<form method="POST" action="options.php">
    <?php
    settings_fields('sample-page');
    do_settings_sections('sample-page');
    submit_button();
    ?>
</form>