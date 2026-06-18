<?php
// Include the database connection
include 'database/db_connect.php'; 
include 'component/navbar_links.php'; 
include 'message.php'; 

// Get package ID from URL
$packageId = $_GET['id'] ?? null;

if (!$packageId) {
    header('Location: package.php');
    exit();
}

// Fetch package details with all related information
try {
    // Get main package details
    $packageSql = "SELECT p.* FROM packages p WHERE p.id = ?";
    $stmt = $pdo->prepare($packageSql);
    $stmt->execute([$packageId]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        header('Location: package.php');
        exit();
    }
    
    // Try to get images if package_images table exists
    $images = [];
    try {
        $imageSql = "SELECT image_name FROM package_images WHERE package_id = ? ORDER BY sort_order";
        $imageStmt = $pdo->prepare($imageSql);
        $imageStmt->execute([$packageId]);
        $imageResults = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($imageResults) {
            foreach ($imageResults as $result) {
                $images[] = $result['image_name'];
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist, use single image field
        $images = [$package['image'] ?? 'default.jpg'];
    }
    
    // Fetch itinerary details
    $itinerarySql = "SELECT * FROM itinerary_details WHERE package_id = ? ORDER BY day_number";
    $itineraryStmt = $pdo->prepare($itinerarySql);
    $itineraryStmt->execute([$packageId]);
    $itinerary = $itineraryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch package inclusions/exclusions
    $inclusions = [];
    $exclusions = [];
    try {
        $inclusionsSql = "SELECT * FROM package_inclusions WHERE package_id = ? ORDER BY inclusion_type, item";
        $inclusionsStmt = $pdo->prepare($inclusionsSql);
        $inclusionsStmt->execute([$packageId]);
        $allInclusions = $inclusionsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Separate inclusions and exclusions
        foreach ($allInclusions as $item) {
            if ($item['inclusion_type'] === 'inclusion') {
                $inclusions[] = $item['item'];
            } else {
                $exclusions[] = $item['item'];
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist, use package fields
        if ($package['includes']) {
            $inclusions = explode(', ', $package['includes']);
        }
        if ($package['excludes']) {
            $exclusions = explode(', ', $package['excludes']);
        }
    }
    
    // Get related packages (exclude current)
    $relatedSql = "SELECT * FROM packages WHERE id != ? ORDER BY RAND() LIMIT 3";
    $relatedStmt = $pdo->prepare($relatedSql);
    $relatedStmt->execute([$packageId]);
    $relatedPackages = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error fetching package details: " . $e->getMessage());
}
?>

<style>
/* Package Details Styles */
.package-details {
    padding: 2rem;
    background: #f8f9fa;
}

.detail-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Hero Section */
.package-hero {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    margin-bottom: 3rem;
}

.image-gallery {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-thumbnails {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    background: rgba(0, 0, 0, 0.7);
    padding: 10px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: #667eea;
    transform: scale(1.1);
}

.package-badges {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.badge {
    background: rgba(255, 255, 255, 0.95);
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

.duration-badge {
    color: #667eea;
}

.difficulty-badge {
    color: white;
}

.difficulty-easy { background: #28a745; }
.difficulty-moderate { background: #ffc107; }
.difficulty-challenging { background: #dc3545; }

/* Package Info Section */
.package-info {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.info-main {
    background: white;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.package-title {
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 1rem;
    font-weight: 700;
}

.package-price {
    font-size: 2rem;
    color: #667eea;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.package-price span {
    font-size: 1rem;
    color: #6c757d;
    font-weight: 400;
}

.package-description {
    color: #6c757d;
    line-height: 1.8;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.package-features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-2px);
}

.feature-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.feature-text h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1rem;
}

.feature-text p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Booking Sidebar */
.booking-sidebar {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.booking-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.booking-card h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    text-align: center;
}

.price-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
}

.price-row.total {
    font-weight: 700;
    font-size: 1.2rem;
    color: #667eea;
    border-top: 2px solid #dee2e6;
    padding-top: 0.8rem;
    margin-top: 1rem;
}

.booking-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-group input {
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.btn-book {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
}

.btn-book:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

/* Itinerary Section */
.itinerary-section {
    background: white;
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 2rem;
    text-align: center;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.itinerary-timeline {
    position: relative;
    padding-left: 3rem;
}

.itinerary-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.itinerary-day {
    position: relative;
    margin-bottom: 3rem;
}

.day-marker {
    position: absolute;
    left: -35px;
    top: 0;
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.day-content {
    background: #f8f9fa;
    padding: 4rem;
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.day-title {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 1rem;
    font-weight: 600;
}

.day-description {
    color: #6c757d;
    line-height: 2;
    margin-bottom: 1.5rem;
}

.day-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.day-detail {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #667eea;
}

.day-detail strong {
    color: #667eea;
    display: block;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.day-detail span {
    color: #495057;
    line-height: 1.6;
    font-size: 1.5rem;
}

/* Inclusions/Exclusions Section */
.inclusions-section {
    background: white;
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 3rem;
}

.inclusions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
}

.inclusion-list,
.exclusion-list {
    padding: 2rem;
    border-radius: 10px;
}

.inclusion-list {
    background: #d4edda;
}

.exclusion-list {
    background: #f8d7da;
}

.inclusion-list h4,
.exclusion-list h4 {
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.inclusion-list h4 {
    color: #155724;
}

.exclusion-list h4 {
    color: #721c24;
}

.inclusion-list ul,
.exclusion-list ul {
    list-style: none;
    padding: 0;
}

.inclusion-list li,
.exclusion-list li {
    padding: 0.8rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    font-size: 1rem;
}

.inclusion-list li:last-child,
.exclusion-list li:last-child {
    border-bottom: none;
}

.inclusion-list li::before {
    content: "✓ ";
    color: #155724;
    font-weight: bold;
    font-size: 1.2rem;
}

.exclusion-list li::before {
    content: "✗ ";
    color: #721c24;
    font-weight: bold;
    font-size: 1.2rem;
}

/* Related Packages */
.related-section {
    background: white;
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.related-card {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.related-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.related-image {
    height: 200px;
    overflow: hidden;
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-content {
    padding: 1.5rem;
}

.related-title {
    font-size: 1.2rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.related-price {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 1rem;
}

/* Responsive Design */
@media (max-width: 968px) {
    .package-info {
        grid-template-columns: 1fr;
    }
    
    .booking-sidebar {
        position: static;
    }
    
    .package-features {
        grid-template-columns: 1fr;
    }
    
    .inclusions-grid {
        grid-template-columns: 1fr;
    }
    
    .day-details {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .package-details {
        padding: 1rem;
    }
    
    .info-main,
    .itinerary-section,
    .inclusions-section,
    .related-section {
        padding: 1.5rem;
    }
    
    .package-title {
        font-size: 2rem;
    }
    
    .image-gallery {
        height: 300px;
    }
}
</style>

<!-- Package Details -->
<div class="package-details">
    <div class="detail-container">
        
        <!-- Hero Section with Image Gallery -->
        <div class="package-hero">
            <div class="image-gallery">
                <?php 
                $mainImage = !empty($images) ? $images[0] : ($package['image'] ?? 'default.jpg');
                ?>
                <img src="images/<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>" class="main-image" id="mainImage">
                
                <?php if (!empty($images) && count($images) > 1): ?>
                <div class="image-thumbnails">
                    <?php foreach ($images as $index => $image): ?>
                    <img src="images/<?php echo htmlspecialchars($image); ?>" 
                         alt="Thumbnail <?php echo $index + 1; ?>" 
                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                         onclick="changeImage('<?php echo htmlspecialchars($image); ?>', this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="package-badges">
                    <div class="badge duration-badge">
                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($package['duration_days'] ?? 'N/A'); ?> Days
                    </div>
                    <div class="badge difficulty-badge difficulty-<?php echo htmlspecialchars($package['difficulty_level'] ?? 'easy'); ?>">
                        <i class="fas fa-signal"></i> <?php echo ucfirst(htmlspecialchars($package['difficulty_level'] ?? 'easy')); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Package Info Section -->
        <div class="package-info">
            <div class="info-main">
                <h1 class="package-title"><?php echo htmlspecialchars($package['title']); ?></h1>
                <div class="package-price">
                    $<?php echo number_format($package['price'], 2); ?>
                    <span>/ person</span>
                </div>
                
                <p class="package-description">
                    <?php echo htmlspecialchars($package['description']); ?>
                </p>
                
                <div class="package-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Duration</h4>
                            <p><?php echo htmlspecialchars($package['duration_days'] ?? 'N/A'); ?> Days</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-signal"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Difficulty</h4>
                            <p><?php echo ucfirst(htmlspecialchars($package['difficulty_level'] ?? 'easy')); ?></p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Accommodation</h4>
                            <p><?php echo htmlspecialchars($package['accommodation_type'] ?? 'Standard'); ?></p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Transportation</h4>
                            <p><?php echo htmlspecialchars($package['transportation'] ?? 'Included'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Booking Sidebar -->
            <div class="booking-sidebar">
                <div class="booking-card">
                    <h3>Book This Package</h3>
                    
                    <div class="price-summary">
                        <div class="price-row">
                            <span>Base Price:</span>
                            <span>$<?php echo number_format($package['price'], 2); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Duration:</span>
                            <span><?php echo htmlspecialchars($package['duration_days'] ?? 'N/A'); ?> Days</span>
                        </div>
                        <div class="price-row total">
                            <span>Starting From:</span>
                            <span>$<?php echo number_format($package['price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <form action="book.php" method="get" class="booking-form">
                        <input type="hidden" name="package" value="<?php echo htmlspecialchars($package['title']); ?>">
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($package['price']); ?>">
                        
                        <div class="form-group">
                            <label for="guests">Number of Guests</label>
                            <input type="number" id="guests" name="guests" min="1" value="1" required>
                        </div>
                        
                        <button type="submit" class="btn-book">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Itinerary Section -->
        <?php if (!empty($itinerary)): ?>
        <div class="itinerary-section">
            <h2 class="section-title">Detailed Itinerary</h2>
            
            <div class="itinerary-timeline">
                <?php foreach ($itinerary as $day): ?>
                <div class="itinerary-day">
                    <div class="day-marker">Day <?php echo $day['day_number']; ?></div>
                    <div class="day-content">
                        <h3 class="day-title"><?php echo htmlspecialchars($day['title']); ?></h3>
                        <p class="day-description"><?php echo htmlspecialchars($day['description']); ?></p>
                        
                        <div class="day-details">
                            <?php if (!empty($day['meals'])): ?>
                            <div class="day-detail">
                                <strong><i class="fas fa-utensils"></i> Meals</strong>
                                <span><?php echo nl2br(htmlspecialchars($day['meals'])); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($day['activities'])): ?>
                            <div class="day-detail">
                                <strong><i class="fas fa-hiking"></i> Activities</strong>
                                <span><?php echo nl2br(htmlspecialchars($day['activities'])); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($day['accommodation'])): ?>
                            <div class="day-detail">
                                <strong><i class="fas fa-bed"></i> Accommodation</strong>
                                <span><?php echo nl2br(htmlspecialchars($day['accommodation'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Inclusions/Exclusions Section -->
        <?php if (!empty($inclusions) || !empty($exclusions) || $package['includes'] || $package['excludes']): ?>
        <div class="inclusions-section">
            <h2 class="section-title">What's Included & Excluded</h2>
            
            <div class="inclusions-grid">
                <div class="inclusion-list">
                    <h4><i class="fas fa-check-circle"></i> What's Included</h4>
                    <ul>
                        <?php
                        if (!empty($inclusions)) {
                            foreach ($inclusions as $item) {
                                echo '<li>' . htmlspecialchars($item) . '</li>';
                            }
                        } elseif ($package['includes']) {
                            $includes = explode(', ', $package['includes']);
                            foreach ($includes as $item) {
                                echo '<li>' . htmlspecialchars(trim($item)) . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="exclusion-list">
                    <h4><i class="fas fa-times-circle"></i> What's Excluded</h4>
                    <ul>
                        <?php
                        if (!empty($exclusions)) {
                            foreach ($exclusions as $item) {
                                echo '<li>' . htmlspecialchars($item) . '</li>';
                            }
                        } elseif ($package['excludes']) {
                            $excludes = explode(', ', $package['excludes']);
                            foreach ($excludes as $item) {
                                echo '<li>' . htmlspecialchars(trim($item)) . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Related Packages Section -->
        <?php if (!empty($relatedPackages)): ?>
        <div class="related-section">
            <h2 class="section-title">You Might Also Like</h2>
            
            <div class="related-grid">
                <?php foreach ($relatedPackages as $related): ?>
                <div class="related-card">
                    <div class="related-image">
                        <img src="images/<?php echo htmlspecialchars($related['image'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($related['title']); ?>">
                    </div>
                    <div class="related-content">
                        <h3 class="related-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                        <div class="related-price">$<?php echo number_format($related['price'], 2); ?> / person</div>
                        <a href="package_details.php?id=<?php echo $related['id']; ?>" class="btn-book">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
function changeImage(imageSrc, thumbnail) {
    document.getElementById('mainImage').src = 'images/' + imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

// Smooth scroll for internal navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php include 'component/footer.php'; ?>
