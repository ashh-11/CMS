<?php
$page_title = "Contact Us";
$breadcrumbs = [
    ["Home", "user_dashboard.php"],
    ["Contact Us", "#"]
];
$is_public_landing_page = true; // Set this to true to hide sidebar toggle and search bar
include('inc.header.php');

$name = $email = $subject = $message_content = '';
$name_err = $email_err = $subject_err = $message_content_err = '';
$form_success_message = '';
$form_error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject'] ?? ''));
    $message_content = mysqli_real_escape_string($conn, trim($_POST['message'] ?? ''));

    if (empty($name)) { $name_err = "Please enter your name."; }
    if (empty($email)) { $email_err = "Please enter your email."; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $email_err = "Invalid email format."; }
    if (empty($subject)) { $subject_err = "Please enter a subject."; }
    if (empty($message_content)) { $message_content_err = "Please enter your message."; }

    if (empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_content_err)) {
        // This is where you would typically SEND an email.
        // For a beginner-style project without an email sending setup,
        // we'll just simulate success and log it to the database if you had a contact_messages table.
        // For now, we'll just set a success message.

        $form_success_message = "<div class='alert alert-success' style='background-color: #7AAEEA; border-color: #7AAEEA; color: white;'>Your message has been sent. Thank you!</div>";

        // Optional: Log to a dummy table or just email yourself.
        // Example if you had a 'contact_messages' table:
        // $sql_log_contact = "INSERT INTO contact_messages (name, email, subject, message_content, received_at) VALUES ('$name', '$email', '$subject', '$message_content', NOW())";
        // if (!mysqli_query($conn, $sql_log_contact)) {
        //    error_log("Failed to log contact message: " . mysqli_error($conn));
        // }

        // Clear form fields after successful submission
        $name = $email = $subject = $message_content = '';

    } else {
        $form_error_message = "<div class='alert alert-danger' style='background-color: #FE6A53; border-color: #FE6A53; color: white;'>Please correct the errors in the form.</div>";
    }
}
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
              <h5 class="card-title" style="color: #5A90D7;">Get in Touch with TrackIt Couriers!</h5>
              <p>
                Have questions about your shipment, need assistance, or want to provide feedback? Our dedicated team at TrackIt Couriers is here to help! Fill out the form below, and we'll get back to you as soon as possible.
              </p>
              <p>
                You can also reach us directly at:
                <br><strong>Phone:</strong> 021-1111234 (Karachi Head Office)
                <br><strong>Email:</strong> info@trackitcouriers.pk
                <br><strong>Address:</strong> Shahrah-e-Faisal, Karachi, Pakistan
              </p>

              <?php echo $form_success_message; ?>
              <?php echo $form_error_message; ?>

              <form class="row g-3" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="col-md-12">
                  <label for="inputName" class="form-label">Your Name</label>
                  <input type="text" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" id="inputName" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                  <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <div class="col-md-12">
                  <label for="inputEmail" class="form-label">Your Email</label>
                  <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="inputEmail" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                  <div class="invalid-feedback"><?php echo $email_err; ?></div>
                </div>
                <div class="col-12">
                  <label for="inputSubject" class="form-label">Subject</label>
                  <input type="text" class="form-control <?php echo (!empty($subject_err)) ? 'is-invalid' : ''; ?>" id="inputSubject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                  <div class="invalid-feedback"><?php echo $subject_err; ?></div>
                </div>
                <div class="col-12">
                  <label for="inputMessage" class="form-label">Message</label>
                  <textarea class="form-control <?php echo (!empty($message_content_err)) ? 'is-invalid' : ''; ?>" id="inputMessage" name="message" rows="5" required><?php echo htmlspecialchars($message_content); ?></textarea>
                  <div class="invalid-feedback"><?php echo $message_content_err; ?></div>
                </div>
                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-primary" style="background-color: #7AAEEA; border-color: #7AAEEA; color: white;">Send Message</button>
                  <button type="reset" class="btn btn-secondary ms-2" style="background-color: #819CDD; border-color: #819CDD; color: white;">Reset</button>
                </div>
              </form>
            </div>
          </div>
        </section>
      </div>

      <div class="col-lg-6 d-flex align-items-center justify-content-center main-content-right">
        <img src="assets/img/contact.png" alt="Contact Us Illustration" class="img-fluid" style="max-height: 70vh;">
      </div>

    </div>
  </main>

<?php include('inc.footer.php'); ?>

<style>
  html, body {
    height: 100%;
    margin: 0;
    background-color: #E5D1CF;
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
    margin: 20px auto;
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
  .card-body strong {
    color: #5A90D7;
  }

  label {
    color: #3B428A;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
  }

  .form-control, .form-select, textarea {
    border: 1px solid #819CDD;
    border-radius: 8px;
    padding: 10px;
    color: #3B428A;
  }
  .form-control:focus, .form-select:focus, textarea:focus {
    border-color: #7AAEEA;
    box-shadow: 0 0 0 0.25rem rgba(122, 174, 234, 0.25);
  }
  .form-control.is-invalid, .form-select.is-invalid, textarea.is-invalid {
    border-color: #FE6A53;
  }
  .invalid-feedback {
    color: #FE6A53;
  }


  .btn-primary {
      background-color: #7AAEEA !important;
      border-color: #7AAEEA !important;
      color: #FFFFFF !important;
      box-shadow: 0 4px 8px rgba(90, 144, 215, 0.2);
  }
  .btn-primary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
      box-shadow: 0 6px 12px rgba(90, 144, 215, 0.3);
  }
  .btn-secondary {
      background-color: #819CDD !important;
      border-color: #819CDD !important;
      color: #FFFFFF !important;
      box-shadow: 0 4px 8px rgba(129, 156, 221, 0.2);
  }
  .btn-secondary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
      box-shadow: 0 6px 12px rgba(90, 144, 215, 0.3);
  }


  #header .logo svg path,
  #header .logo svg rect {
      fill: #FFFFFF !important;
  }
  #header .logo svg text {
      fill: #FFFFFF !important;
  }
  #header .logo:hover svg path,
  #header .logo:hover svg rect,
  #header .logo:hover svg text {
      fill: #E5D1CF !important;
  }

  #header .toggle-sidebar-btn {
      color: #FFFFFF;
  }
  #header .toggle-sidebar-btn:hover {
      color: #E5D1CF;
  }

  .search-bar input {
      color: #FFFFFF;
      background: transparent;
      border: none;
  }
  .search-bar input::placeholder {
      color: rgba(255, 255, 255, 0.7);
  }
  .search-bar button {
      background: transparent;
      border: none;
      color: #E5D1CF;
  }
  .search-bar button:hover {
      color: #FFFFFF;
  }

  #header .header-nav .nav-link {
      color: #FFFFFF !important;
  }
  #header .header-nav .nav-link:hover {
      color: #E5D1CF !important;
  }
  #header .nav-profile .dropdown-toggle {
      color: #FFFFFF !important;
  }
  #header .dropdown-menu {
      background-color: #5A90D7;
      border: 1px solid #819CDD;
  }
  #header .dropdown-menu .dropdown-header h6,
  #header .dropdown-menu .dropdown-header span {
      color: #E5D1CF;
  }
  #header .dropdown-menu .dropdown-item {
      color: #FFFFFF;
  }
  #header .dropdown-menu .dropdown-item:hover {
      background-color: #819CDD;
      color: #FFFFFF;
  }
  #header .dropdown-menu .dropdown-item i {
      color: #7AAEEA;
  }

  .notifications .badge-number {
      background-color: #FE6A53 !important;
  }

  .main-content-right img {
    max-width: 100%;
    height: auto;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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