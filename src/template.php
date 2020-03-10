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

<div class="dc-hello-bar dc-hello-bar--closed">
  <div class="dc-hello-bar__content"></div>
  <a class="dc-hello-bar__close"></a>
</div>

<script>
  /* Manage closing ad bar, and keeping it closed for session duration */
  if (!sessionStorage.getItem('dcHelloBarClosed')) {
    dcHydrateHello(); // Adds content to hello bar from back end, and makes it visible
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

  /* Add ad content to dc-hello-bar__content. Ad filtering & selection is handled on back end */
  function dcHydrateHello() {
    // XMLHttpRequest because IE is still a thing
    var dcXML = new XMLHttpRequest();
    dcXML.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        // slice response text because it includes protected quotes
        document.querySelector('.dc-hello-bar__content').innerHTML = JSON.parse(dcXML.responseText);
        document.querySelector('.dc-hello-bar').classList.remove('dc-hello-bar--closed');
      }
    }
    dcXML.open('GET', '<?php echo get_home_url(); ?>/wp-json/dc-hello/v1/get-ad');
    dcXML.send();
  }
</script>
