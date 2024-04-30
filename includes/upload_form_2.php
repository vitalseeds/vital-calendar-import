<h1>
    <?php esc_html_e('Import data', PLUGIN_DOMAIN); ?>
</h1>
<p><?php esc_html_e('Import the data?', PLUGIN_DOMAIN); ?></p>

<?php if ($errors) { ?>
    <h2>Errors</h2>
    <pre><?php print_r($errors) ?></pre>
<?php } else { ?>
    <h2>Successful upload</h2>
    <p>There were no errors.</p>
<?php } ?>

<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" id="form-step" name="step" value="2">
    <?php submit_button("Import") ?>
</form>

<h2>Headers</h2>
<ul>
    <?php foreach ($headers as $header) { ?>
        <li><?php echo $header ?></li>
    <?php } ?>
</ul>

<h2>Data</h2>
<pre><?php print_r($data) ?></pre>