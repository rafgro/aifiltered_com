<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preload" href="./fa/webfonts/fa-regular-400.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="./fa/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="dns-prefetch" href="https://www.google-analytics.com">
  <title>Audio Processing - AI filtered</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="normalize.min.css">
  <link href="https://fonts.googleapis.com/css?family=DM+Sans:400,500,700&amp;display=swap" rel="stylesheet">
  <link href="./fa/css/all.min.css" rel="stylesheet">
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-176592011-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-176592011-1');
  </script>
</head>

<body>
<div class="app-wrapper">
  <div class="left-area hide-on-mobile">
    <div class="app-header"><a href="https://aifiltered.com" style="color:#000000">AI
      <span class="inner-text">filtered</span></a>
      <button class="close-menu">
        <svg width="24" height="24" fill="none" stroke="#51a380" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="feather feather-x">
          <defs />
          <path d="M18 6L6 18M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="left-area-content">
      <div class="profile" style="margin-right:52px">
        <div class="profile-info">
          <span class="profile-name">updated:<br/><?php include('./hour.php'); ?></span>
        </div>
      </div>
      <div class="list-header">
        <span class="">Categories</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="16" />
          <line x1="8" y1="12" x2="16" y2="12" /></svg>
      </div>
      <a href="./cv" class="item-link" id="pageLink">
        <svg class="link-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" /></svg>
        Computer Vision</a>
      <a href="./audio" class="item-link active" id="pageLink">
        <svg class="link-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" /></svg>
        Audio Processing</a>
      <a href="./nlp" class="item-link" id="pageLink">
        <svg class="link-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" /></svg>
        Natural Language Processing</a>
      <a href="./rl" class="item-link" id="pageLink">
        <svg class="link-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" /></svg>
        Reinforcement Learning</a>
      <div class="list-header">
        <span class="">Other content</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="16" />
          <line x1="8" y1="12" x2="16" y2="12" /></svg>
      </div>
      <a href="./podcasts" class="item-link" id="pageLink">
        <svg class="link-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" /></svg>
        Podcasts</a>
      <a href="./education" class="item-link" id="pageLink">
        <svg class="link-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" /></svg>
        Education</a>
    </div>
    <button class="btn-invite" onClick="window.location.reload();">Refresh</button>
  </div>
  <div class="right-area">
    <div class="right-area-upper">
      <button class="menu-button">
        <svg width="24" height="24" fill="none" stroke="#51a380" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
          <defs />
          <path d="M3 12h18M3 6h18M3 18h18" />
        </svg>
      </button>
      <div class="search-part-wrapper">
        <div class="search-input">Audio Processing</div>
        <a class="menu-links" href="./month">Last month</a>
        <a class="menu-links" href="./about">About</a>
        <button class="more-button">
          <svg width="24" height="24" fill="none" stroke="#51a380" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="feather feather-more-vertical">
            <defs />
            <circle cx="12" cy="12" r="1" />
            <circle cx="12" cy="5" r="1" />
            <circle cx="12" cy="19" r="1" />
          </svg>
        </button>
        <ul class="more-menu-list hide">
          <li><a href="./month">Last month</a></li>
          <li><a href="./about">About</a></li>
        </ul>
      </div>
    </div>
    <div class="page-right-content" id="innercontent" tabindex="0">
      <div class="content-line content-line-list">
        <div class="line-header">
          <span class="header-text">Trending in the last 24h</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; padding: 32px 10px 8px 10px;">
          <?php include('./Audiostream24h.php'); ?>
        </div>
      </div>
      <div class="content-line content-line-list">
        <div class="line-header">
          <span class="header-text">Last week, sorted by date</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; padding: 32px 10px 8px 10px;">
          <?php include('./Audiostream7d.php'); ?>
        </div>
      </div>
      <div class="content-line content-line-list">
        <div class="line-header">
          <span class="header-text">Last month, sorted by date</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; padding: 32px 10px 8px 10px;">
          <?php include('./Audiostream1m.php'); ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://ajax.aspnetcdn.com/ajax/jquery/jquery-3.5.1.min.js"></script>
  <script src='script.js'></script>
  <script>
    document.querySelector('#innercontent').focus();
  </script>

</body>

</html>