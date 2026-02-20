<?php
// pet_details.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if pet ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('pets.php');
}

$pet_id = (int)$_GET['id'];
$pet = getPetById($conn, $pet_id);

// If pet not found, redirect
if (!$pet) {
    redirect('pets.php');
}

// Fetch all images for this pet
$stmt_imgs = $conn->prepare("SELECT image_url FROM pet_images WHERE pet_id = ?");
$stmt_imgs->execute([$pet['pet_id']]);
$all_images = $stmt_imgs->fetchAll(PDO::FETCH_COLUMN);
if (empty($all_images) && !empty($pet['image_url'])) $all_images = [$pet['image_url']];

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <?php if (count($all_images) > 1): ?>
                <div id="petGallery" class="carousel slide mb-3" data-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($all_images as $idx => $img): ?>
                            <div class="carousel-item<?php if ($idx === 0) echo ' active'; ?>">
                                <img src="<?php echo htmlspecialchars($img); ?>" class="d-block w-100 rounded" alt="Pet image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="carousel-control-prev" href="#petGallery" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#petGallery" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            <?php else: ?>
                <?php if (!empty($all_images[0])): ?>
                    <img src="<?php echo htmlspecialchars($all_images[0]); ?>" class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                <?php else: ?>
                    <img src="assets/images/pet-placeholder.jpg" class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <h2>
                <?php echo htmlspecialchars($pet['name']); ?>
                <?php if (isLoggedIn()): ?>
                    <?php $fav = isFavorite($conn, $_SESSION['user_id'], $pet['pet_id']); ?>
                    <button class="favorite-btn" data-pet-id="<?php echo $pet['pet_id']; ?>" aria-label="Favorite" style="background:none; border:none; outline:none; margin-left:10px; vertical-align:middle;">
                        <i class="fas fa-heart<?php echo $fav ? '' : '-o'; ?>" style="color:<?php echo $fav ? '#e2a15d' : '#bbb'; ?>; font-size:1.7rem;"></i>
                    </button>
                <?php endif; ?>
            </h2>

            <div class="mb-3">
                <span class="badge badge-primary"><?php echo htmlspecialchars($pet['category_name']); ?></span>
                <span class="badge badge-info"><?php echo htmlspecialchars($pet['breed']); ?></span>
                <span class="badge badge-secondary"><?php echo htmlspecialchars($pet['age'] ?? 'Unknown'); ?><?php if (!empty($pet['age_number'])): ?> (<?php echo (int)$pet['age_number']; ?> years)<?php endif; ?></span>
                <span class="badge badge-dark"><?php echo htmlspecialchars($pet['size']); ?></span>
                <span class="badge badge-warning"><?php echo htmlspecialchars($pet['gender']); ?></span>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>About <?php echo htmlspecialchars($pet['name']); ?></h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($pet['description'])); ?></p>
                </div>
            </div>

            <?php if (isLoggedIn() && !isAdminRole($conn, $_SESSION['user_id'])): ?>
                <!-- Removed Ask About This Pet button and contact modal -->
            <?php endif; ?>

            <?php if ($pet['is_adopted']): ?>
                <div class="alert alert-warning">
                    <strong>Already Adopted</strong> - This pet has found a forever home.
                </div>
            <?php elseif (isLoggedIn()): ?>
                <a href="apply.php?pet_id=<?php echo $pet['pet_id']; ?>" class="btn btn-primary btn-lg btn-block">Apply to Adopt</a>
            <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="login.php">login</a> or <a href="register.php">register</a> to send an adoption request.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Social Sharing Buttons -->
<div class="social-sharing mt-4 mb-4" style="display: flex; gap: 12px; align-items: center;">
    <button class="btn btn-outline-primary" id="shareBtn" type="button"><i class="fas fa-share-alt"></i> Share</button>
</div>
<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareModalLabel">Share this pet</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <button class="btn btn-light mb-2" id="copyLinkBtn" style="width: 90%; max-width: 350px;"><i class="fas fa-link"></i> Copy Link</button>
        <a class="btn btn-light mb-2" href="mailto:?subject=Check out <?php echo rawurlencode($pet['name']); ?> for adoption!&body=<?php echo rawurlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" style="width: 90%; max-width: 350px;" target="_blank"><i class="fas fa-envelope"></i> Gmail</a>
        <a class="btn btn-light mb-2" href="https://www.instagram.com/" target="_blank" style="width: 90%; max-width: 350px;"><i class="fab fa-instagram"></i> Instagram</a>
        <button class="btn btn-light mb-2" id="nativeShareBtn" style="width: 90%; max-width: 350px;"><i class="fas fa-share-square"></i> More...</button>
        <div id="copyLinkSuccess" class="alert alert-success mt-3" style="display:none;">Link copied!</div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('shareBtn').addEventListener('click', function() {
    $('#shareModal').modal('show');
});
document.getElementById('copyLinkBtn').addEventListener('click', function() {
    var url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        var success = document.getElementById('copyLinkSuccess');
        success.style.display = 'block';
        setTimeout(function() { success.style.display = 'none'; }, 2000);
    });
});
document.getElementById('nativeShareBtn').addEventListener('click', function() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        });
    } else {
        alert('Your browser does not support the native share feature.');
    }
});
</script>

<?php if (isLoggedIn() && !isAdminRole($conn, $_SESSION['user_id'])): ?>
<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" id="contactForm">
        <div class="modal-header">
          <h5 class="modal-title" id="contactModalLabel">Ask About <?php echo htmlspecialchars($pet['name']); ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="message">Your Question</label>
            <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send Message</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Success Alert -->
<div id="messageSentAlert" class="alert alert-success text-center" style="display:none; position: fixed; top: 30px; left: 50%; transform: translateX(-50%); z-index: 2000; min-width: 300px; max-width: 90%; box-shadow: 0 2px 12px rgba(0,0,0,0.12);">
  <i class="fas fa-check-circle"></i> Your message has been sent!
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const petId = this.getAttribute('data-pet-id');
        const icon = this.querySelector('i');
        fetch('toggle_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'pet_id=' + encodeURIComponent(petId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.favorited) {
                    icon.classList.remove('fa-heart-o');
                    icon.classList.add('fa-heart');
                    icon.style.color = '#e2a15d';
                } else {
                    icon.classList.remove('fa-heart');
                    icon.classList.add('fa-heart-o');
                    icon.style.color = '#bbb';
                }
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
<script>
console.log('Attaching contact form handler...');
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    formData.append('pet_id', <?php echo (int)$pet['pet_id']; ?>);
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            form.reset();
            $('#contactModal').modal('hide');
            // Show the success alert
            var alertBox = document.getElementById('messageSentAlert');
            alertBox.style.display = 'block';
            setTimeout(function() {
                alertBox.style.display = 'none';
            }, 3500);
        } else {
            alert('Error: ' + (data.error || 'Could not send message.'));
        }
    });
});
</script>