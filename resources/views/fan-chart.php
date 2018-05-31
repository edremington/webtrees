<?= $html ?>

<map id="fan-chart-map" name="fan-chart-map">
	<?= $areas ?>
</map>

<div class="text-center">
	<img class="wt-fan-chart-img" src="data:image/png;base64,<?= base64_encode($png) ?>" width="<?= $fanw ?>" height="<?= $fanh ?>" alt="<?= strip_tags($title) ?>" usemap="#fan-chart-map">
</div>

<script>
  jQuery("area")
    .click(function (e) {
      e.stopPropagation();
      e.preventDefault();
      var target = jQuery(this.hash);
      // position the menu centered immediately above the mouse click position and
      // make sure it doesn’t end up off the screen
      target
        .css({
          left: Math.max(0, e.pageX - (target.outerWidth() / 2)),
          top:  Math.max(0, e.pageY - target.outerHeight())
        })
        .toggle()
        .siblings(".fan_chart_menu").hide();
    });
  jQuery(".fan_chart_menu")
    .on("click", "a", function (e) {
      e.stopPropagation();
    });
  jQuery("#fan_chart")
    .click(function (e) {
      jQuery(".fan_chart_menu").hide();
    });
</script>
