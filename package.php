<?php
// Include the database connection
include 'database/db_connect.php'; 
include 'component/navbar_links.php'; 
include 'message_enhanced.php'; 

// Fetch packages with itinerary details from the database using PDO
try {
    $sql = "SELECT p.*, 
                   (SELECT COUNT(*) FROM itinerary_details WHERE package_id = p.id) as has_itinerary,
                   (SELECT GROUP_CONCAT(image_name ORDER BY sort_order) FROM package_images WHERE package_id = p.id) as images
            FROM packages p 
            ORDER BY p.price ASC";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch itinerary details for each package
    foreach ($packages as &$package) {
        $itinerarySql = "SELECT * FROM itinerary_details WHERE package_id = ? ORDER BY day_number";
        $itineraryStmt = $pdo->prepare($itinerarySql);
        $itineraryStmt->execute([$package['id']]);
        $package['itinerary'] = $itineraryStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error fetching packages: " . $e->getMessage());
}
?>

<style>
/* Enhanced Package Styles */
.packages {
    padding: 2rem;
    background: #f8f9fa;
}

.heading-title {
    text-align: center;
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 3rem;
    position: relative;
}

.heading-title::after {
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

.box-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.package-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
}

.package-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.package-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.package-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.package-card:hover .package-image img {
    transform: scale(1.05);
}

.duration-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.95);
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #667eea;
    backdrop-filter: blur(10px);
}

.difficulty-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.difficulty-easy { background: #28a745; }
.difficulty-moderate { background: #ffc107; }
.difficulty-challenging { background: #dc3545; }

.package-content {
    padding: 1.5rem;
}

.package-title {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.package-price {
    font-size: 1.8rem;
    color: #667eea;
    font-weight: 700;
    margin-bottom: 1rem;
}

.package-price span {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 400;
}

.package-description {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.package-meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #495057;
    font-size: 0.9rem;
}

.meta-item i {
    color: #667eea;
    width: 20px;
}

.package-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    flex: 1;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f8f9fa;
    color: #667eea;
    border: 2px solid #667eea;
    flex: 1;
}

.btn-secondary:hover {
    background: #667eea;
    color: white;
}

/* Itinerary Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: white;
    margin: 2% auto;
    padding: 0;
    border-radius: 15px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    transition: transform 0.3s ease;
}

.close:hover {
    transform: rotate(90deg);
}

.modal-body {
    padding: 2rem;
    max-height: 70vh;
    overflow-y: auto;
}

.itinerary-day {
    margin-bottom: 2rem;
    border-left: 4px solid #667eea;
    padding-left: 1.5rem;
    position: relative;
}

.day-number {
    position: absolute;
    left: -20px;
    top: 0;
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.day-title {
    font-size: 1.3rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.day-description {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.day-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.detail-item {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
}

.detail-item strong {
    color: #667eea;
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.inclusions-exclusions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.inclusion-list, .exclusion-list {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
}

.inclusion-list h4 {
    color: #28a745;
    margin-bottom: 1rem;
}

.exclusion-list h4 {
    color: #dc3545;
    margin-bottom: 1rem;
}

.inclusion-list ul, .exclusion-list ul {
    list-style: none;
    padding: 0;
}

.inclusion-list li, .exclusion-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.inclusion-list li:last-child, .exclusion-list li:last-child {
    border-bottom: none;
}

.inclusion-list li::before {
    content: "✓ ";
    color: #28a745;
    font-weight: bold;
}

.exclusion-list li::before {
    content: "✗ ";
    color: #dc3545;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .box-container {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
    
    .package-meta {
        grid-template-columns: 1fr;
    }
    
    .inclusions-exclusions {
        grid-template-columns: 1fr;
    }
    
    .day-details {
        grid-template-columns: 1fr;
    }
}

/* Custom scrollbar for modal */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<!-- header section ends -->
<div class="heading" style="background:url(images/header-bg-2.png) no-repeat">
    <h1>Travel Packages</h1>
</div>

<!-- packages section starts  -->
<section class="packages">

   <h1 class="heading-title">Discover Amazing Destinations</h1>

   <div class="box-container">

   <?php
   // Check if there are any packages
   if (!empty($packages)) {
       // Loop through each package and display it
       foreach ($packages as $package) {
           $images = explode(',', $package['images']);
           $primaryImage = $images[0] ?? 'default.jpg';
           ?>
           <div class="package-card">
               <div class="package-image">
                   <img src="images/<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>">
                   <div class="duration-badge"><?php echo htmlspecialchars($package['duration_days']); ?> Days</div>
                   <div class="difficulty-badge difficulty-<?php echo htmlspecialchars($package['difficulty_level']); ?>">
                       <?php echo ucfirst(htmlspecialchars($package['difficulty_level'])); ?>
                   </div>
               </div>
               <div class="package-content">
                   <h3 class="package-title"><?php echo htmlspecialchars($package['title']); ?></h3>
                   <div class="package-price">
                       $<?php echo number_format($package['price'], 2); ?>
                       <span>/ person</span>
                   </div>
                   <p class="package-description"><?php echo htmlspecialchars(substr($package['description'], 0, 150)); ?>...</p>
                   
                   <div class="package-meta">
                       <div class="meta-item">
                           <i class="fas fa-home"></i>
                           <span><?php echo htmlspecialchars($package['accommodation_type']); ?></span>
                       </div>
                       <div class="meta-item">
                           <i class="fas fa-bus"></i>
                           <span><?php echo htmlspecialchars($package['transportation']); ?></span>
                       </div>
                   </div>
                   
                   <div class="package-actions">
                       <a href="book_enhanced.php?package=<?php echo urlencode($package['title']); ?>&price=<?php echo urlencode($package['price']); ?>" class="btn btn-primary">Book Now</a>
                       <a href="package_details.php?id=<?php echo $package['id']; ?>" class="btn btn-secondary">View Details</a>
                   </div>
               </div>
           </div>
           <?php
       }
   } else {
       echo "<div style='text-align: center; grid-column: 1/-1; padding: 3rem;'><h3>No packages available.</h3></div>";
   }
   ?>

   </div>

</section>
<!-- packages section ends -->

<!-- Itinerary Modal -->
<div id="itineraryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Package Itinerary</h2>
            <button class="close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Itinerary content will be loaded here -->
        </div>
    </div>
</div>

<script>
// Package data from PHP
const packageData = <?php echo json_encode($packages); ?>;

function showItinerary(packageId) {
    const package = packageData.find(p => p.id == packageId);
    if (!package) return;
    
    document.getElementById('modalTitle').textContent = package.title + ' - Detailed Itinerary';
    
    let itineraryHtml = '';
    
    // Package overview
    itineraryHtml += `
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">Package Overview</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div><strong>Duration:</strong> ${package.duration_days} Days</div>
                <div><strong>Difficulty:</strong> ${package.difficulty_level}</div>
                <div><strong>Accommodation:</strong> ${package.accommodation_type}</div>
                <div><strong>Transportation:</strong> ${package.transportation}</div>
                <div><strong>Price:</strong> $${parseFloat(package.price).toFixed(2)} / person</div>
            </div>
        </div>
    `;
    
    // Itinerary details
    if (package.itinerary && package.itinerary.length > 0) {
        itineraryHtml += '<h3 style="color: #2c3e50; margin-bottom: 1.5rem;">Day-by-Day Itinerary</h3>';
        
        package.itinerary.forEach(day => {
            itineraryHtml += `
                <div class="itinerary-day">
                    <div class="day-number">${day.day_number}</div>
                    <div class="day-title">${day.title}</div>
                    <div class="day-description">${day.description}</div>
                    <div class="day-details">
                        ${day.meals ? `<div class="detail-item"><strong>Meals:</strong> ${day.meals}</div>` : ''}
                        ${day.activities ? `<div class="detail-item"><strong>Activities:</strong> ${day.activities}</div>` : ''}
                        ${day.accommodation ? `<div class="detail-item"><strong>Accommodation:</strong> ${day.accommodation}</div>` : ''}
                    </div>
                </div>
            `;
        });
    }
    
    // Inclusions and Exclusions
    if (package.includes || package.excludes) {
        itineraryHtml += '<div class="inclusions-exclusions">';
        
        if (package.includes) {
            const inclusions = package.includes.split(', ').map(item => `<li>${item.trim()}</li>`).join('');
            itineraryHtml += `
                <div class="inclusion-list">
                    <h4><i class="fas fa-check-circle"></i> What's Included</h4>
                    <ul>${inclusions}</ul>
                </div>
            `;
        }
        
        if (package.excludes) {
            const exclusions = package.excludes.split(', ').map(item => `<li>${item.trim()}</li>`).join('');
            itineraryHtml += `
                <div class="exclusion-list">
                    <h4><i class="fas fa-times-circle"></i> What's Excluded</h4>
                    <ul>${exclusions}</ul>
                </div>
            `;
        }
        
        itineraryHtml += '</div>';
    }
    
    document.getElementById('modalBody').innerHTML = itineraryHtml;
    document.getElementById('itineraryModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('itineraryModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('itineraryModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'component/footer.php'; ?>
