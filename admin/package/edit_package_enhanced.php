<?php
include('../../database/db_connect.php');

if (!isset($_GET['id'])) {
    die("Package ID not provided.");
}

$id = $_GET['id'];

try {
    // Get package details
    $sql = "SELECT * FROM packages WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        die("Package not found.");
    }
    
    // Get itinerary details for this package
    $itinerarySql = "SELECT * FROM itinerary_details WHERE package_id = :package_id ORDER BY day_number";
    $itineraryStmt = $pdo->prepare($itinerarySql);
    $itineraryStmt->bindParam(':package_id', $id);
    $itineraryStmt->execute();
    $itinerary = $itineraryStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error fetching package details: " . $e->getMessage());
}

include '../component/nav_admin.php';
?>

<style>
.enhanced-form {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.form-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.form-section h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #495057;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.itinerary-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 2rem;
}

.itinerary-item {
    background: white;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    border-left: 4px solid #667eea;
}

.itinerary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.day-number {
    background: #667eea;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
}

.remove-day {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
}

.add-day-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 1rem;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: transform 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="main-content flex-grow-1 p-4">
    <div class="header mb-4">
        <h1 class="h3">Edit Package - Enhanced</h1>
    </div>
    
    <form action="update_package_process.php" method="POST" enctype="multipart/form-data" class="enhanced-form">
        <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
        
        <div class="form-grid">
            <!-- Basic Information -->
            <div class="form-section">
                <h3>📋 Basic Information</h3>
                
                <div class="form-group">
                    <label for="title">Package Title *</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($package['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price per Person ($) *</label>
                    <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($package['price']); ?>" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea name="description" id="description" class="form-control" rows="4" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Package Image</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    <small>Current image: <?php echo htmlspecialchars($package['image']); ?></small>
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($package['image']); ?>">
                </div>
            </div>
            
            <!-- Package Details -->
            <div class="form-section">
                <h3>🏔️ Package Details</h3>
                
                <div class="form-group">
                    <label for="duration_days">Duration (Days) *</label>
                    <input type="number" name="duration_days" id="duration_days" class="form-control" value="<?php echo htmlspecialchars($package['duration_days'] ?? ''); ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="difficulty_level">Difficulty Level *</label>
                    <select name="difficulty_level" id="difficulty_level" class="form-control" required>
                        <option value="easy" <?php echo ($package['difficulty_level'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                        <option value="moderate" <?php echo ($package['difficulty_level'] == 'moderate') ? 'selected' : ''; ?>>Moderate</option>
                        <option value="challenging" <?php echo ($package['difficulty_level'] == 'challenging') ? 'selected' : ''; ?>>Challenging</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="accommodation_type">Accommodation Type *</label>
                    <input type="text" name="accommodation_type" id="accommodation_type" class="form-control" value="<?php echo htmlspecialchars($package['accommodation_type'] ?? ''); ?>" placeholder="e.g., Mountain Teahouses, 3-Star Hotels">
                </div>
                
                <div class="form-group">
                    <label for="transportation">Transportation *</label>
                    <input type="text" name="transportation" id="transportation" class="form-control" value="<?php echo htmlspecialchars($package['transportation'] ?? ''); ?>" placeholder="e.g., Private vehicle, Domestic flight">
                </div>
                
                <div class="form-group">
                    <label for="includes">What's Included *</label>
                    <textarea name="includes" id="includes" class="form-control" rows="3" placeholder="e.g., Experienced guide, All meals, Accommodation, Trekking permits"><?php echo htmlspecialchars($package['includes'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="excludes">What's Excluded *</label>
                    <textarea name="excludes" id="excludes" class="form-control" rows="3" placeholder="e.g., Personal expenses, Tips, International flights"><?php echo htmlspecialchars($package['excludes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Itinerary Section -->
        <div class="itinerary-section">
            <h3>📅 Day-by-Day Itinerary</h3>
            <div id="itinerary-container">
                <?php foreach ($itinerary as $index => $day): ?>
                <div class="itinerary-item" data-day="<?php echo $day['day_number']; ?>">
                    <div class="itinerary-header">
                        <span class="day-number">Day <?php echo $day['day_number']; ?></span>
                        <button type="button" class="remove-day" onclick="removeDay(this)">✕ Remove</button>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Day Title *</label>
                            <input type="text" name="day_title[]" class="form-control" value="<?php echo htmlspecialchars($day['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="day_description[]" class="form-control" rows="3" required><?php echo htmlspecialchars($day['description']); ?></textarea>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label>Meals</label>
                            <input type="text" name="day_meals[]" class="form-control" value="<?php echo htmlspecialchars($day['meals'] ?? ''); ?>" placeholder="e.g., Breakfast, Lunch, Dinner">
                        </div>
                        <div class="form-group">
                            <label>Activities</label>
                            <input type="text" name="day_activities[]" class="form-control" value="<?php echo htmlspecialchars($day['activities'] ?? ''); ?>" placeholder="e.g., Forest trekking, River crossing">
                        </div>
                        <div class="form-group">
                            <label>Accommodation</label>
                            <input type="text" name="day_accommodation[]" class="form-control" value="<?php echo htmlspecialchars($day['accommodation'] ?? ''); ?>" placeholder="e.g., Namche Lodge">
                        </div>
                    </div>
                    
                    <input type="hidden" name="day_id[]" value="<?php echo $day['id']; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="add-day-btn" onclick="addNewDay()">➕ Add New Day</button>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <button type="submit" class="btn-primary">💾 Update Package</button>
        </div>
    </form>
</div>

<script>
function removeDay(button) {
    if (confirm('Are you sure you want to remove this day from the itinerary?')) {
        button.closest('.itinerary-item').remove();
    }
}

function addNewDay() {
    const container = document.getElementById('itinerary-container');
    const dayCount = container.children.length + 1;
    
    const newDay = document.createElement('div');
    newDay.className = 'itinerary-item';
    newDay.innerHTML = `
        <div class="itinerary-header">
            <span class="day-number">Day ${dayCount}</span>
            <button type="button" class="remove-day" onclick="removeDay(this)">✕ Remove</button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Day Title *</label>
                <input type="text" name="day_title[]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="day_description[]" class="form-control" rows="3" required></textarea>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div class="form-group">
                <label>Meals</label>
                <input type="text" name="day_meals[]" class="form-control" placeholder="e.g., Breakfast, Lunch, Dinner">
            </div>
            <div class="form-group">
                <label>Activities</label>
                <input type="text" name="day_activities[]" class="form-control" placeholder="e.g., Forest trekking, River crossing">
            </div>
            <div class="form-group">
                <label>Accommodation</label>
                <input type="text" name="day_accommodation[]" class="form-control" placeholder="e.g., Namche Lodge">
            </div>
        </div>
        
        <input type="hidden" name="day_id[]" value="">
    `;
    
    container.appendChild(newDay);
}

// Auto-save functionality
let autoSaveTimer;
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('input', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            console.log('Auto-saving...');
        }, 2000);
    });
});
</script>
