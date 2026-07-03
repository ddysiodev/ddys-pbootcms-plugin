<?php
?><!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo ddys_open_h($title); ?></title>
  <?php echo $assets; ?>
</head>
<body class="ddys-pbootcms-page">
  <main class="ddys-pbootcms-shell">
    <header class="ddys-pbootcms-page-header">
      <a class="ddys-pbootcms-brand" href="<?php echo ddys_open_attr(ddys_open_page_url('latest')); ?>">
        <img src="<?php echo ddys_open_attr(ddys_open_static_url('images/logo.png')); ?>" alt="" width="32" height="32" />
        <span>低端影视</span>
      </a>
      <?php echo $nav; ?>
    </header>
    <section class="ddys-pbootcms-page-content">
      <?php echo $content; ?>
    </section>
  </main>
</body>
</html>

