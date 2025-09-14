<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/functions.php';

$pageTitle = 'Logistip - Home';
include __DIR__ . '/partials/header.php';

$services = [];
$portfolios = [];
$plans = [];
$brands = [];
$testimonials = [];
$posts = [];

// Fetch data with graceful fallback if DB not initialized yet
try {
  $services = getServices(6);
  $portfolios = getPortfolios(6);
  $plans = getPricingPlans();
  $brands = getBrands(10);
  $testimonials = getTestimonials(5);
  $posts = getPosts(3);
} catch (Throwable $e) {
  // Show a small hint in dev mode
  echo '<div class="container mt-3"><div class="alert alert-warning">Database not initialized yet. Import database/schema.sql. Error: ' . esc($e->getMessage()) . '</div></div>';
}
?>

    <!-- Hero -->
    <section class="section-hero hero-logistics position-relative overflow-hidden">
      <div class="hero-bg"></div>
      <div class="container position-relative">
        <div class="row align-items-center py-6">
          <div class="col-lg-7 text-white">
            <h1 class="display-4 fw-bold mb-3">Trusted Transport<br>Logistic Company</h1>
            <p class="lead text-white-50 mb-4">With our commitment, excellence and customer satisfaction, we streamline your supply chain and drive your business.</p>
            <div class="d-flex align-items-center gap-3 mb-4">
              <a href="#quote" class="btn btn-primary btn-lg">Rate & Ship <i class="fa-solid fa-arrow-up-right-from-square ms-2"></i></a>
              <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="btn btn-light btn-lg rounded-circle p-0 d-inline-flex align-items-center justify-content-center video-popup" style="width:56px;height:56px"><i class="fa-solid fa-play"></i></a>
            </div>
            <div class="row g-3 mt-3 mini-services">
              <div class="col-12 col-sm-4">
                <div class="mini-service-card text-center p-2">
                  <img src="https://images.unsplash.com/photo-1529070538774-1843cb3265df?q=80&w=400" class="rounded w-100" alt="Air Freight" style="height:120px;object-fit:cover">
                  <div class="small pt-2">Air Freight Services</div>
                </div>
              </div>
              <div class="col-12 col-sm-4">
                <div class="mini-service-card text-center p-2">
                  <img src="https://images.unsplash.com/photo-1569025690938-a00729c9e1ea?q=80&w=400" class="rounded w-100" alt="Road Freight" style="height:120px;object-fit:cover">
                  <div class="small pt-2">Road Freight Services</div>
                </div>
              </div>
              <div class="col-12 col-sm-4">
                <div class="mini-service-card text-center p-2">
                  <img src="https://images.unsplash.com/photo-1558885544-2defc0629ad2?q=80&w=400" class="rounded w-100" alt="Ocean Freight" style="height:120px;object-fit:cover">
                  <div class="small pt-2">Ocean Freight Services</div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-5 position-relative d-none d-lg-block">
            <div class="hero-circle">
              <img class="hero-circle-img" src="https://images.unsplash.com/photo-1554473675-6815a55acd12?q=80&w=1000" alt="Containers">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About -->
    <section id="about" class="section-about about-2 py-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <img class="img-fluid rounded-3" src="https://picsum.photos/800/500?random=1" alt="About us">
          </div>
          <div class="col-lg-6">
            <h2 class="h1 mb-3">About <?php echo esc(SITE_NAME); ?></h2>
            <p>We are a logistics company offering freight, warehousing, and last-mile delivery solutions. Our mission is to provide reliable and efficient services tailored to your needs.</p>
            <ul class="list-unstyled">
              <li class="mb-2"><i class="fa fa-check text-primary me-2"></i>Global coverage</li>
              <li class="mb-2"><i class="fa fa-check text-primary me-2"></i>Real-time tracking</li>
              <li class="mb-2"><i class="fa fa-check text-primary me-2"></i>Expert support</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Services -->
    <section id="services" class="section-service service-3 hover-element py-5 bg-light">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h2 class="h1 mb-0">Our Services</h2>
          <a href="#" class="btn btn-outline-secondary">View all</a>
        </div>
        <div class="row g-4">
          <?php if ($services): foreach ($services as $s): ?>
            <div class="col-12 col-sm-6 col-lg-4">
              <div class="card h-100 shadow-sm">
                <div class="card-body">
                  <div class="display-6 mb-3 text-primary"><i class="<?php echo esc($s['icon_class'] ?: 'fa fa-truck'); ?>"></i></div>
                  <h3 class="h5"><?php echo esc($s['title']); ?></h3>
                  <p class="mb-0 text-secondary"><?php echo esc($s['summary']); ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">No services yet. Add some in the database.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Portfolio -->
    <section class="section-portfolio portfolio-1 hover-element py-5">
      <div class="container">
        <h2 class="h1 mb-4">Recent Projects</h2>
        <div class="row g-4">
          <?php if ($portfolios): foreach ($portfolios as $p): ?>
            <div class="col-12 col-sm-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm">
                <img src="<?php echo esc($p['image_url']); ?>" class="card-img-top" alt="<?php echo esc($p['title']); ?>">
                <div class="card-body">
                  <span class="badge bg-secondary mb-2"><?php echo esc($p['category']); ?></span>
                  <h3 class="h5 mb-0"><?php echo esc($p['title']); ?></h3>
                </div>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">No portfolio items yet.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section class="section-pricing pricing-1 hover-element py-5 bg-light">
      <div class="container">
        <h2 class="h1 mb-4">Pricing</h2>
        <div class="row g-4">
          <?php if ($plans): foreach ($plans as $pl): ?>
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card h-100 shadow-sm <?php echo $pl['is_featured'] ? 'border-primary' : ''; ?>">
                <div class="card-body">
                  <h3 class="h5 mb-1"><?php echo esc($pl['name']); ?></h3>
                  <div class="display-6 fw-bold mb-3">$<?php echo esc($pl['price']); ?><small class="fs-6 text-secondary">/<?php echo esc($pl['period']); ?></small></div>
                  <ul class="list-unstyled small mb-4">
                    <?php foreach (explode("\n", (string)$pl['features']) as $f): if (trim($f) === '') continue; ?>
                      <li class="mb-1"><i class="fa fa-check text-success me-2"></i><?php echo esc($f); ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <a href="#quote" class="btn btn-<?php echo $pl['is_featured'] ? 'primary' : 'outline-primary'; ?> w-100">Choose plan</a>
                </div>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">No pricing plans yet.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Brand carousel -->
    <div class="section-brand brand-2 position-relative py-5 bg-white">
      <div class="container">
        <div class="swiper brandSwiper">
          <div class="swiper-wrapper">
            <?php if ($brands): foreach ($brands as $b): ?>
              <div class="swiper-slide d-flex align-items-center justify-content-center"><img src="<?php echo esc($b['logo_url']); ?>" alt="<?php echo esc($b['name']); ?>" style="max-height:50px"></div>
            <?php endforeach; else: ?>
              <div class="swiper-slide">Add brand logos to see the carousel.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Testimonials -->
    <section class="section-testimonial py-5 bg-light">
      <div class="container">
        <h2 class="h1 mb-4">What clients say</h2>
        <div class="swiper testimonialSwiper-3">
          <div class="swiper-wrapper">
            <?php if ($testimonials): foreach ($testimonials as $t): ?>
              <div class="swiper-slide">
                <div class="card shadow-sm">
                  <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                      <img src="<?php echo esc($t['avatar_url']); ?>" class="rounded-circle me-3" style="width:48px;height:48px;object-fit:cover" alt="<?php echo esc($t['author_name']); ?>">
                      <div>
                        <div class="fw-semibold"><?php echo esc($t['author_name']); ?></div>
                        <div class="text-secondary small"><?php echo esc($t['author_role']); ?></div>
                      </div>
                    </div>
                    <p class="mb-0">“<?php echo esc($t['content']); ?>”</p>
                  </div>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div class="swiper-slide"><div class="alert alert-info m-0">No testimonials yet.</div></div>
            <?php endif; ?>
          </div>
          <div class="testimonialSwiper-swiper-pagination mt-3"></div>
        </div>
      </div>
    </section>

    <!-- Blog -->
    <section class="section-blog blog-3 py-5">
      <div class="container">
        <h2 class="h1 mb-4">Latest from our blog</h2>
        <div class="row g-4">
          <?php if ($posts): foreach ($posts as $post): ?>
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm">
                <img src="<?php echo esc($post['image_url']); ?>" class="card-img-top" alt="<?php echo esc($post['title']); ?>">
                <div class="card-body">
                  <div class="small text-secondary mb-2"><?php echo date('M j, Y', strtotime($post['published_at'])); ?></div>
                  <h3 class="h5"><?php echo esc($post['title']); ?></h3>
                  <p class="text-secondary"><?php echo esc($post['excerpt']); ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">No blog posts yet.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Call Request (Quote form) -->
    <section id="quote" class="section-callRequest py-5 bg-primary text-white">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="col-lg-6">
            <h2 class="h1 mb-3">Request a Call Back</h2>
            <p class="mb-0">Tell us about your shipment and we will contact you shortly.</p>
          </div>
          <div class="col-lg-6">
            <form method="post" class="row g-3">
              <div class="col-md-4">
                <input required type="text" name="cr_name" class="form-control" placeholder="Your name">
              </div>
              <div class="col-md-4">
                <input required type="tel" name="cr_phone" class="form-control" placeholder="Phone">
              </div>
              <div class="col-md-4">
                <button class="btn btn-dark w-100" type="submit" name="cr_submit">Request</button>
              </div>
              <div class="col-12">
                <textarea name="cr_message" class="form-control" rows="2" placeholder="Message (optional)"></textarea>
              </div>
            </form>
            <?php
              if (!empty($_POST['cr_submit'])) {
                  $ok = false;
                  try { $ok = saveCallRequest(trim($_POST['cr_name']), trim($_POST['cr_phone']), trim($_POST['cr_message'] ?? '')); } catch (Throwable $e) {}
                  echo '<div class="mt-3 alert ' . ($ok ? 'alert-success' : 'alert-danger') . '">' . ($ok ? 'Request submitted!' : 'Failed to submit.') . '</div>';
              }
            ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Subscription -->
    <section class="section-subscription bg-custom-light py-5">
      <div class="container">
        <div class="row align-items-center g-3">
          <div class="col-lg-6">
            <h2 class="h3 mb-2">Subscribe for updates</h2>
            <p class="mb-0 text-secondary">Get news, offers, and logistics tips.</p>
          </div>
          <div class="col-lg-6">
            <form method="post" class="d-flex gap-2">
              <input required type="email" name="sub_email" class="form-control" placeholder="Your email">
              <button class="btn btn-primary" type="submit" name="sub_submit">Subscribe</button>
            </form>
            <?php
              if (!empty($_POST['sub_submit'])) {
                  $ok = false;
                  try { $ok = saveSubscription(trim($_POST['sub_email'])); } catch (Throwable $e) {}
                  echo '<div class="mt-2 alert ' . ($ok ? 'alert-success' : 'alert-danger') . '">' . ($ok ? 'Subscribed!' : 'Failed to subscribe.') . '</div>';
              }
            ?>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/partials/footer.php';
