    <!-- Footer -->
    <footer class="section-footer footer-1 bg-secondary" data-bs-theme="dark">
      <div class="container py-5 text-white-50">
        <div class="row">
          <div class="col-md-6">
            <h5 class="text-white mb-3"><?php echo esc(SITE_NAME); ?></h5>
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo esc(SITE_NAME); ?>. All rights reserved.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a class="text-white-50 me-3" href="#">Privacy</a>
            <a class="text-white-50" href="#">Terms</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Back-to-top -->
    <div class="progressCounter progressScroll"><!-- TODO: progress indicator --></div>

    <!-- Map Modal -->
    <div class="modal modal-fullscreen routing-map-modal" id="RoutingMapModal"><!-- TODO: map modal --></div>

    <!-- SVG Sprite (icons) -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none"><!-- TODO: icons --></svg>
  </div>

  <!-- Core JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/wowjs@1.1.3/dist/wow.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/magnific-popup/dist/jquery.magnific-popup.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/odometer@0.4.8/odometer.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

  <!-- Init -->
  <script src="assets/js/main.js"></script>
</body>
</html>
