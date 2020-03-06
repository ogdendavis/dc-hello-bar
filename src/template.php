<?php
/**
 * The template for displaying custom hello bar
 *
 * Inserted at the bottom of the <body> in every page, when the Session variable hello_bar is set to True
 *
 */

?>

<style>
  <?php include 'styles.css'; ?>
</style>

<?php
  // Get date in month and day of week, and format content from that!
  date_default_timezone_set('America/New_York');
  $dc_hello_date = date('d');
  $dc_hello_day = date('D');

  // Get ad choices

  $dc_hello_content = 'It is ' . $dc_hello_day . ' the ' . $dc_hello_date . 'th.';
?>

<div class="dc-hello-bar">
  <div class="dc-hello-bar__content">
    <?php echo esc_html($dc_hello_content) ?>
  </div>
  <a class="dc-hello-bar__close"></a>
</div>

<script>
  if (!sessionStorage.getItem('dcHelloBarClosed')) {
    sessionStorage.setItem('dcHelloBarClosed', false);
    document.querySelector('.dc-hello-bar__close').addEventListener('click', dcCloseHello);
  }
  else {
    document.querySelector('.dc-hello-bar').remove();
  }

  function dcCloseHello() {
    // Set session variable so that hello bar doesn't show on subsequent pages
    sessionStorage.setItem('dcHelloBarClosed', true);
    // Now hide the hello bar
    document.querySelector('.dc-hello-bar').classList.add('dc-hello-bar--closed');
  }
</script>
