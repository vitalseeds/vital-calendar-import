<h1>
    <?php esc_html_e('CSV data imported', PLUGIN_DOMAIN); ?>
</h1>

<h2>Import complete</h2>
<p><strong><?php echo count($results); ?> categories were updated</strong></p>

<ul>
    <?php foreach ($results as $term_id => $cat_name) : ?>
        <li>
            <?php $cat_url = get_site_url(null, "wp-admin/term.php?taxonomy=product_cat&tag_ID=$term_id", 'https'); ?>
            <a href="<?php echo $cat_url; ?>"> <?php echo $cat_name; ?></a>
        </li>
    <?php endforeach; ?>
</ul>