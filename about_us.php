<?php
$page_title = "About Us";
$breadcrumbs = [
    ["Home", "user_dashboard.php"],
    ["About Us", "#"]
];
$is_public_landing_page = true; // Set this to true to hide sidebar toggle and search bar
include('inc.header.php');
?>
  <main id="main" class="main">
    <div class="row align-items-center justify-content-center h-100">

      <div class="col-lg-6 main-content-left">
        <div class="pagetitle">
          <h1><?php echo htmlspecialchars($page_title); ?></h1>
          <nav>
            <ol class="breadcrumb">
              <?php foreach ($breadcrumbs as $crumb) { ?>
                <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($crumb[1]); ?>"><?php echo htmlspecialchars($crumb[0]); ?></a></li>
              <?php } ?>
            </ol>
          </nav>
        </div>

        <section class="section">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title" style="color: #5A90D7;">Our Story: Connecting Pakistan, One Parcel at a Time</h5>
              <p>
                TrackIt Couriers was founded in [Year, e.g., 2015] with a simple mission: to provide reliable, efficient, and affordable courier services across Pakistan. From the bustling streets of Karachi to the scenic valleys of Gilgit, we envisioned a network that truly connects every corner of our beautiful nation.
              </p>
              <p>
                What started as a small local operation in Lahore has grown into a nationwide presence, serving thousands of businesses and individuals daily. We understand the heartbeat of Pakistan – the urgency of sending gifts to loved ones, the critical delivery of business documents, and the excitement of online shopping. That's why we're committed to ensuring every parcel reaches its destination safely and on time.
              </p>
              <h5 class="card-title mt-4" style="color: #5A90D7;">Our Values</h5>
              <ul>
                <li><strong>Reliability:</strong> We pride ourselves on dependable service.</li>
                <li><strong>Speed:</strong> Delivering with efficiency is at our core.</li>
                <li><strong>Customer Focus:</strong> Your satisfaction is our priority.</li>
                <li><strong>Integrity:</strong> Honesty and transparency in every transaction.</li>
                <li><strong>Innovation:</strong> Constantly improving our services through technology.</li>
              </ul>
              <p>
                We are more than just a courier service; we are a partner in your daily life, ensuring that distance is never a barrier. Join us on our journey to deliver excellence, every single day.
              </p>
            </div>
          </div>
        </section>
      </div>

      <div class="col-lg-6 d-flex align-items-center justify-content-center main-content-right">
        <img src="assets/img/pakistan_delivery.jpg" alt="Pakistan Delivery Network Illustration" class="img-fluid" style="max-height: 80vh;">
      </div>

    </div>
  </main>

<?php include('inc.footer.php'); ?>

<style>
  html, body {
    height: 100%;
    margin: 8px;
    font-family: 'Open Sans', sans-serif;
    color: #3B428A;
  }

  body {
    display: flex;
    flex-direction: column;
  }

  #main {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background-color: #FFFFFF;
    margin: 30px auto ;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    max-width: 1300px;
  }

  .main-content-left {
    padding-right: 30px;
  }
  .main-content-right {
    padding-left: 30px;
  }

  .pagetitle h1 {
    color: #5A90D7;
    font-weight: 700;
    text-align: left;
    margin-bottom: 15px;
    font-size: 2.2em;
  }

  .breadcrumb {
    justify-content: flex-start;
    background-color: transparent;
    padding: 0;
    margin-bottom: 20px;
  }
  .breadcrumb .breadcrumb-item a {
    color: #7AAEEA;
  }
  .breadcrumb .breadcrumb-item.active {
    color: #5A90D7;
    font-weight: 600;
  }

  .card {
    margin: 16px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border-radius: 15px;
    background-color: #FFFFFF;
  }
  .card-title {
    color: #5A90D7;
    font-weight: 700;
    margin-bottom: 15px;
  }
  .card-body p, .card-body li {
    color: #819CDD;
    line-height: 1.6;
    margin-bottom: 10px;
  }
  .card-body ul {
    list-style-type: disc;
    padding-left: 20px;
  }
  .card-body strong {
    color: #5A90D7;
  }

  @media (max-width: 992px) {
    .main-content-left, .main-content-right {
      padding: 0 15px;
      flex: 0 0 100%;
      max-width: 100%;
    }
    .main-content-right {
      margin-top: 30px;
    }
  }
</style>