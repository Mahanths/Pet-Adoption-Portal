<?php
// index.php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Fetch up to 6 available pets from the database
try {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM pets p JOIN pet_categories c ON p.category_id = c.category_id WHERE p.is_adopted = 0 ORDER BY p.created_at DESC LIMIT 6");
    $stmt->execute();
    $featured_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featured_pets = [];
}

// Example stats (replace with DB queries if needed)
$totalPets = 120;

// Set up an array of visually fitting images for featured pets
$pet_images = [
    'pexels-artem-makarov-289670876-13224675.jpg',
    'pexels-lilartsy-2397448.jpg',
    'pexels-kowalievska-1416792.jpg',
    'pexels-jennifer-murray-402778-1090408.jpg',
    'pexels-julia-volk-6408365.jpg',
    'pexels-goochie-poochie-3361739.jpg',
    'pexels-catscoming-1359307.jpg',
    'pexels-anastasiya-gepp-654466-1462636.jpg',
    'pexels-roman-odintsov-12715266.jpg',
    'pexels-caio-56733.jpg',
];

?>

<style>
.hero-section.hero-png-bg {
    background: linear-gradient(rgba(255,255,255,0.6), rgba(255,255,255,0.6)), url('assets/images/my_img/cute-pet-collage-isolated.jpg') center -400px/cover no-repeat;
}
</style>

<!-- Hero Section with PNG background and overlay -->
<section class="hero-section hero-png-bg d-flex align-items-center">
    <div class="hero-overlay"></div>
    <div class="container hero-content position-relative">
        <h1 class="hero-title">Find Your New Best Friend</h1>
        <p class="hero-subtitle">Adopt a loving pet and give them a forever home. Start your journey today!</p>
        <a href="pets.php" class="btn btn-primary hero-btn">Browse Available Pets</a>
    </div>
</section>

<!-- Featured Pets Section -->
<section class="featured-pets">
    <div class="container">
        <div class="section-title">
            <h2>Featured Pets</h2>
            <p>Meet some of our adorable pets waiting for a loving family.</p>
        </div>
        <div class="pet-grid">
            <?php if (!empty($featured_pets)): ?>
                <?php foreach ($featured_pets as $pet): ?>
                    <div class="pet-card">
                        <div class="pet-image">
                            <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'assets/images/pet-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($pet['name'] ?? 'Pet'); ?>">
                        </div>
                        <div class="pet-info">
                            <h3><?php echo htmlspecialchars($pet['name'] ?? 'Unknown'); ?></h3>
                            <div class="pet-meta">
                                <span><i class="fas fa-paw"></i> <?php echo htmlspecialchars($pet['category_name'] ?? 'Unknown'); ?></span>
                                <span><i class="fas fa-venus-mars"></i> <?php echo htmlspecialchars($pet['gender'] ?? 'Unknown'); ?></span>
                                <span><i class="fas fa-birthday-cake"></i> 
                                <?php 
                                if (!empty($pet['age_number']) && $pet['age_number'] > 0) {
                                    echo '(' . (int)$pet['age_number'] . ' years)';
                                } else {
                                    echo htmlspecialchars($pet['age'] ?? '');
                                }
                                ?>
                                </span>
                            </div>
                            <p class="pet-description"><?php echo htmlspecialchars(substr($pet['description'] ?? '', 0, 80)) . '...'; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center w-100 py-5">
                    <p class="text-muted" style="font-size:1.2rem;">No available pets to feature at the moment. Please check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Awareness Section (beige/orange theme, no white box) -->
<section class="awareness-section" style="background: #f8f5f2; padding: 2.5rem 0; text-align: center;">
    <p style="font-size:1.25rem; color:#7a5a2b; margin-bottom:0; line-height:1.7; max-width: 900px; margin-left:auto; margin-right:auto;">
        <span style="font-weight:700; color:var(--main-orange);">Did you know?</span> Every year, millions of pets lose their lives simply because they don't have a home. By choosing to adopt, you're giving a second chance to a loving animal and helping to save lives.
    </p>
</section>

<!-- Call to Action Section (beige/orange theme, no white box) -->
<section class="cta-section" style="background: #f7e7d0; padding: 4rem 0; text-align: center;">
    <h2 class="cta-title" style="color:#222; font-size:2.5rem; font-weight:700; margin-bottom:1.5rem;">Ready to Make a Difference?</h2>
    <p class="cta-text" style="color:#7a5a2b; font-size:1.15rem; margin-bottom:2.5rem; max-width:600px; margin-left:auto; margin-right:auto;">Adopting a pet changes lives—yours and theirs. Start your adoption journey now and bring happiness home.</p>
    <a href="pets.php" class="btn btn-primary btn-lg" style="background:var(--main-orange); color:#fff; font-size:1.2rem; padding: 0.9rem 2.5rem; border-radius:8px; font-weight:600; box-shadow:0 2px 12px rgba(226,161,93,0.10);">Adopt a Pet</a>
</section>

<?php include 'includes/footer.php'; ?>

<!--
Instructions:
- Place your hero PNG image at assets/images/hero-pet.png
- Place your featured pet PNGs at assets/images/pet1.png, pet2.png, pet3.png, etc.
- Replace the placeholder stats and pet info with real data as needed.
-->