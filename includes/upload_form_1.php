<h1>
    <?php esc_html_e('Upload CSV', PLUGIN_DOMAIN); ?>
</h1>
<p><?php esc_html_e('Upload a CSV of sowing calendar dates.', PLUGIN_DOMAIN); ?></p>

<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" id="form-step" name="step" value="1">
    <input type="file" name="csv_file">
    <?php submit_button("Upload") ?>
</form>