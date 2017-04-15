  <hr>

  <footer class="footer">
      <p class="clearfix">
          &copy; 2017, Thibaut de Gouberville, Guillaume Dubois, Antoine Giraud</p>
      </p>
  </footer>

</div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="<?= $RouteHelper->publicPath ?>js/bootstrap.min.js"></script>
    <?php 
        if (isset($js_for_layout)){
            foreach ($js_for_layout as $v){  ?>

    <script src="<?= $RouteHelper->publicPath ?>js/<?= $v; ?>"></script>

         <?php } 

        }
    ?>

  </body>
</html>