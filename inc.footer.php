<?php
ob_end_flush();

if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>
  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright" style="color: #E5D1CF;">
      &copy; Copyright <strong><span style="color: #7AAEEA;">TrackIt Couriers</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <nav class="footer-nav">
        <ul class="d-flex justify-content-center align-items-center mb-2 list-unstyled">
          <li class="me-4"><a href="about_us.php" style="color: #E5D1CF; text-decoration: none; font-size: 1.1em;">About Us</a></li>
          <li class="me-4"><a href="contact_us.php" style="color: #E5D1CF; text-decoration: none; font-size: 1.1em;">Contact Us</a></li>
        </ul>
      </nav>
      <div class="social-links mt-2">
        <a href="#" class="twitter" style="color: #E5D1CF; margin: 0 8px;"><i class="bi bi-twitter"></i></a>
        <a href="#" class="facebook" style="color: #E5D1CF; margin: 0 8px;"><i class="bi bi-facebook"></i></a>
        <a href="#" class="instagram" style="color: #E5D1CF; margin: 0 8px;"><i class="bi bi-instagram"></i></a>
        <a href="#" class="linkedin" style="color: #E5D1CF; margin: 0 8px;"><i class="bi bi-linkedin"></i></a>
      </div>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center" style="background-color: #5A90D7; color: white;"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/js/main.js"></script>

  <style>
    #footer {
        background-color: #3B428A;
        color: #E5D1CF;
        border-top: none;
        padding: 30px 0; /* Increased padding */
        text-align: center;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        font-size: 1em; /* Base font size */
    }
    #footer .credits {
        font-size: 0.9em;
        margin-top: 15px; /* Increased margin */
    }
    #footer .credits a {
        color: #7EB4F2;
        transition: color 0.3s ease;
    }
    #footer .credits a:hover {
        color: #FFFFFF;
    }

    #footer .footer-nav ul li a {
        font-weight: 500;
        transition: color 0.3s ease;
        font-size: 1.1em; /* Bigger text for nav links */
    }
    #footer .footer-nav ul li a:hover {
        color: #FFFFFF !important;
    }

    .social-links {
        margin-top: 15px; /* Space above social icons */
        margin-bottom: 15px; /* Space below social icons */
    }
    .social-links a {
        font-size: 1.5em; /* Bigger icons */
        display: inline-block;
        color: #E5D1CF; /* Light Pink/Beige for icons */
        line-height: 1;
        margin: 0 10px; /* Spacing between icons */
        transition: color 0.3s ease;
    }
    .social-links a:hover {
        color: #FFFFFF !important; /* White on hover */
    }


    .back-to-top {
        background-color: #5A90D7 !important;
        color: #FFFFFF !important;
        border-radius: 50px;
        width: 45px; /* Slightly larger button */
        height: 45px;
        line-height: 0;
        bottom: 20px;
        right: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .back-to-top i {
        font-size: 26px; /* Larger icon */
        color: #FFFFFF;
        line-height: 0;
    }
    .back-to-top:hover {
        background-color: #7AAEEA !important;
        color: #FFFFFF !important;
    }
  </style>
</body>
</html>