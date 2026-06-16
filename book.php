<?php
include 'component/navbar_links.php';

// Check if the user is logged in and has 'customer' role
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'customer') {
    header('Location: login.php');
    exit();
}

include 'database/db_connect.php';

// Get package details
$packageTitle = $_GET['package'] ?? '';
$packagePrice = $_GET['price'] ?? '';

$packageDetails = null;
$itineraryDetails = [];

if ($packageTitle) {
    try {
        // Fetch package details
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE title = ?");
        $stmt->execute([$packageTitle]);
        $packageDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($packageDetails) {
            // Fetch itinerary details
            $itineraryStmt = $pdo->prepare("SELECT * FROM itinerary_details WHERE package_id = ? ORDER BY day_number");
            $itineraryStmt->execute([$packageDetails['id']]);
            $itineraryDetails = $itineraryStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Error fetching package details: " . $e->getMessage());
    }
}
?>

<style>
.booking-enhanced {
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

.booking-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.booking-form-section, .itinerary-section {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    text-align: center;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.package-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.package-summary h3 {
    margin: 0 0 1rem 0;
    font-size: 1.3rem;
}

.package-meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.package-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.package-meta-item i {
    width: 20px;
}

.book-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.input-group {
    display: flex;
    flex-direction: column;
}

.input-group label {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.input-group input,
.input-group select {
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.input-group input:focus,
.input-group select:focus {
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
    margin-top: 1rem;
}

.btn-book:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

/* Itinerary Section */
.itinerary-preview {
    max-height: 400px;
    overflow-y: auto;
}

.itinerary-day {
    border-left: 4px solid #667eea;
    padding-left: 1rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.day-number {
    position: absolute;
    left: -15px;
    top: 0;
    width: 30px;
    height: 30px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.day-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.day-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.5;
}

.price-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-top: 2rem;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.price-row.total {
    font-weight: 700;
    font-size: 1.2rem;
    color: #667eea;
    border-top: 2px solid #dee2e6;
    padding-top: 0.5rem;
    margin-top: 1rem;
}

/* Responsive Design */
@media (max-width: 968px) {
    .booking-container {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .package-meta {
        grid-template-columns: 1fr;
    }
}

/* Custom scrollbar */
.itinerary-preview::-webkit-scrollbar {
    width: 6px;
}

.itinerary-preview::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.itinerary-preview::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.itinerary-preview::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<div class="heading" style="background:url(images/header-bg-3.png) no-repeat">
   <h1>Book Your Adventure</h1>
</div>

<!-- enhanced booking section starts  -->
<section class="booking-enhanced">

   <div class="booking-container">
       
       <!-- Booking Form Section -->
       <div class="booking-form-section">
           <h2 class="section-title">Travel Details</h2>
           
           <?php if ($packageDetails): ?>
           <div class="package-summary">
               <h3><?php echo htmlspecialchars($packageDetails['title']); ?></h3>
               <div class="package-meta">
                   <div class="package-meta-item">
                       <i class="fas fa-clock"></i>
                       <span><?php echo htmlspecialchars($packageDetails['duration_days'] ?? 'N/A'); ?> Days</span>
                   </div>
                   <div class="package-meta-item">
                       <i class="fas fa-signal"></i>
                       <span><?php echo ucfirst(htmlspecialchars($packageDetails['difficulty_level'] ?? 'easy')); ?></span>
                   </div>
                   <div class="package-meta-item">
                       <i class="fas fa-home"></i>
                       <span><?php echo htmlspecialchars($packageDetails['accommodation_type'] ?? 'Standard'); ?></span>
                   </div>
                   <div class="package-meta-item">
                       <i class="fas fa-bus"></i>
                       <span><?php echo htmlspecialchars($packageDetails['transportation'] ?? 'Included'); ?></span>
                   </div>
               </div>
           </div>
           <?php endif; ?>
           
           <form action="book_form.php" method="post" class="book-form">
               <div class="form-row">
                   <div class="input-group">
                       <label for="name">Full Name *</label>
                       <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                   </div>
                   <div class="input-group">
                       <label for="email">Email Address *</label>
                       <input type="email" id="email" name="email" placeholder="your@email.com" required>
                   </div>
               </div>
               
               <div class="form-row">
                   <div class="input-group">
                       <label for="phone">Phone Number *</label>
                       <input type="tel" id="phone" name="phone" placeholder="+1 234 567 8900" required>
                   </div>
                   <div class="input-group">
                       <label for="guests">Number of Guests *</label>
                       <input type="number" id="guests" name="guests" min="1" placeholder="1" required onchange="updatePrice()">
                   </div>
               </div>
               
               <div class="input-group">
                   <label for="address">Address *</label>
                   <input type="text" id="address" name="address" placeholder="Your full address" required>
               </div>
               
               <div class="input-group">
                   <label for="location">Destination *</label>
                   <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($packageTitle); ?>" readonly>
               </div>
               
               <div class="form-row">
                   <div class="input-group">
                       <label for="arrivals">Arrival Date *</label>
                       <input type="date" id="arrivals" name="arrivals" required onchange="updatePrice()">
                   </div>
                   <div class="input-group">
                       <label for="leaving">Departure Date *</label>
                       <input type="date" id="leaving" name="leaving" required onchange="updatePrice()">
                   </div>
               </div>
               
               <!-- Hidden fields -->
               <input type="hidden" name="package" value="<?php echo htmlspecialchars($packageTitle); ?>">
               <input type="hidden" name="price" value="<?php echo htmlspecialchars($packagePrice); ?>">
               <input type="hidden" id="totalPrice" name="totalPrice" value="<?php echo htmlspecialchars($packagePrice); ?>">
               
               <button type="submit" class="btn-book">Complete Booking</button>
           </form>
       </div>
       
       <!-- Itinerary Section -->
       <div class="itinerary-section">
           <h2 class="section-title">Your Itinerary Preview</h2>
           
           <?php if (!empty($itineraryDetails)): ?>
           <div class="itinerary-preview">
               <?php foreach ($itineraryDetails as $day): ?>
               <div class="itinerary-day">
                   <div class="day-number"><?php echo $day['day_number']; ?></div>
                   <div class="day-title"><?php echo htmlspecialchars($day['title']); ?></div>
                   <div class="day-description"><?php echo htmlspecialchars($day['description']); ?></div>
                   <?php if ($day['meals']): ?>
                   <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #667eea;">
                       <i class="fas fa-utensils"></i> <?php echo htmlspecialchars($day['meals']); ?>
                   </div>
                   <?php endif; ?>
               </div>
               <?php endforeach; ?>
           </div>
           <?php else: ?>
           <div style="text-align: center; padding: 2rem; color: #6c757d;">
               <i class="fas fa-calendar-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
               <p>Detailed itinerary will be available after booking confirmation.</p>
           </div>
           <?php endif; ?>
           
           <!-- Price Summary -->
           <div class="price-summary">
               <h4 style="margin-bottom: 1rem; color: #2c3e50;">Price Summary</h4>
               <div class="price-row">
                   <span>Base Price per Person:</span>
                   <span>$<span id="basePrice"><?php echo htmlspecialchars($packagePrice); ?></span></span>
               </div>
               <div class="price-row">
                   <span>Number of Guests:</span>
                   <span id="guestCount">1</span>
               </div>
               <div class="price-row total">
                   <span>Total Amount:</span>
                   <span>$<span id="totalAmount"><?php echo htmlspecialchars($packagePrice); ?></span></span>
               </div>
           </div>
           
           <!-- Package Inclusions -->
           <?php if ($packageDetails && !empty($packageDetails['includes'])): ?>
           <div style="margin-top: 2rem; padding: 1rem; background: #d4edda; border-radius: 8px;">
               <h5 style="color: #155724; margin-bottom: 0.5rem;">What's Included:</h5>
               <p style="color: #155724; font-size: 0.9rem; margin: 0;"><?php echo htmlspecialchars($packageDetails['includes']); ?></p>
           </div>
           <?php endif; ?>
       </div>
   </div>

</section>
<!-- enhanced booking section ends -->

<script>
function updatePrice() {
    const basePrice = parseFloat(document.getElementById('basePrice').textContent);
    const guests = parseInt(document.getElementById('guests').value) || 1;
    const total = basePrice * guests;
    
    document.getElementById('guestCount').textContent = guests;
    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('totalPrice').value = total.toFixed(2);
}

// Set minimum dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('arrivals').setAttribute('min', today);
    document.getElementById('leaving').setAttribute('min', today);
    
    // Ensure leaving date is after arrival date
    document.getElementById('arrivals').addEventListener('change', function() {
        const arrivalDate = new Date(this.value);
        const minDeparture = new Date(arrivalDate);
        minDeparture.setDate(minDeparture.getDate() + 1);
        document.getElementById('leaving').setAttribute('min', minDeparture.toISOString().split('T')[0]);
    });
});
</script>

<?php include 'component/footer.php'; ?>
