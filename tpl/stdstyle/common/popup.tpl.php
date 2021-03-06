<!DOCTYPE html>
<html lang="<?=$GLOBALS['lang']?>">
<head>
  <title><?php echo isset($tpl_subtitle) ? $tpl_subtitle : ''; ?>{title}</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

  <link rel="shortcut icon" href="/images/<?=$config['headerFavicon']?>">
  <link rel="stylesheet" type="text/css" href="tpl/stdstyle/css/main.css">

  <?php foreach( $view->getLocalCss() as $css ) { ?>
    <link rel="stylesheet" type="text/css" href="<?=$css?>">
  <?php } //foreach-css ?>

  {htmlheaders}

  <?php
      if( $view->isGoogleAnalyticsEnabled() ){
          $view->googleAnalyticsChunk( $view->getGoogleAnalyticsKey() );
      }

      if( $view->isjQueryEnabled()){
          $view->callChunk('jQuery');
      }

      if( $view->isjQueryUIEnabled()){
          $view->callChunk('jQueryUI');
      }
      if( $view->isTimepickerEnabled()){
          $view->callChunk('timepicker');
      }
      if( $view->isLightBoxEnabled()){
          $view->callChunk('lightBoxLoader', true, false);
      }
  ?>
</head>
<body{bodyMod}>
  <div id="content">
    {template}
  </div>

  <?php
      // lightbox js should be loaded at th end of page
      if( $view->isLightBoxEnabled()){
          $view->callChunk('lightBoxLoader', false, true);
      }
  ?>
</body>
</html>
