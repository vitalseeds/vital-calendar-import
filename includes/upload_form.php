<h1>
    <?php esc_html_e('Import CSV', PLUGIN_DOMAIN); ?>
</h1>
<p><?php esc_html_e('Import a CSV of sowing calendar dates here.', PLUGIN_DOMAIN); ?></p>

<form action="" method="post" enctype="multipart/form-data">'
    <input type="file" name="csv_file">
    <?php submit_button("Upload") ?>;
    <!-- <input type="submit" name="submit" value="submit"> -->
</form>