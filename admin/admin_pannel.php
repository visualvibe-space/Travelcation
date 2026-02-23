<?php
session_start();
/* ===============================
   DATABASE CONNECTION
================================ */
require_once __DIR__ . '/../config/config.php';
// If not logged in → redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Determine section
$section = $_GET['section'] ?? 'dashboard';

/* ===============================
   TOUR PACKAGES OPERATIONS
================================ */
if (isset($_POST['add_package'])) {
    $img = $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $img);

    // FEATURES AS TEXT
    $features = isset($_POST['features'])
        ? implode(', ', array_map('trim', $_POST['features']))
        : '';
    
    //  LOCATIONS COVERED AS TEXT
    $locations_covered = isset($_POST['locations_covered'])
        ? implode(', ', array_map('trim', $_POST['locations_covered']))
        : '';
    
    //  INCLUSIONS AS TEXT
    $inclusions = isset($_POST['inclusions'])
        ? implode(', ', array_map('trim', $_POST['inclusions']))
        : '';

    $stmt = $pdo->prepare("
        INSERT INTO tour_packages
        (destination_id, title, description, locations_covered, price, price_type, min_people, image, package_type, duration, days, nights, features, inclusions, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $_POST['destination_id'],
        $_POST['title'],
        $_POST['description'],
        $locations_covered,
        $_POST['price'],
        $_POST['price_type'],
        $_POST['min_people'],
        $img,
        $_POST['type'],
        $_POST['duration'],
        $_POST['days'],
        $_POST['nights'],
        $features,
        $inclusions,
        $_POST['status']
    ]);

    header("Location: admin_pannel.php?section=packages&success=Package added successfully");
    exit;
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tour_packages WHERE id=?")->execute([$_GET['delete']]);
    header("Location: admin_pannel.php?section=packages&success=Package deleted successfully");
    exit;
}

if (isset($_POST['update_package'])) {
    $id = $_POST['id'];

    // ✅ FEATURES AS TEXT
    $features = isset($_POST['features'])
        ? implode(', ', array_map('trim', $_POST['features']))
        : '';
    
    // ✅ LOCATIONS COVERED AS TEXT
    $locations_covered = isset($_POST['locations_covered'])
        ? implode(', ', array_map('trim', $_POST['locations_covered']))
        : '';
    
    // ✅ INCLUSIONS AS TEXT
    $inclusions = isset($_POST['inclusions'])
        ? implode(', ', array_map('trim', $_POST['inclusions']))
        : '';

    if (!empty($_FILES['image']['name'])) {
        $img = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $img);

        $sql = "UPDATE tour_packages SET
            destination_id=?, title=?, description=?, locations_covered=?, price=?, price_type=?, min_people=?, image=?, package_type=?,
            duration=?, days=?, nights=?, features=?, inclusions=?, status=? WHERE id=?";
        $data = [
            $_POST['destination_id'],
            $_POST['title'], 
            $_POST['description'],
            $locations_covered,
            $_POST['price'],
            $_POST['price_type'],
            $_POST['min_people'],
            $img, 
            $_POST['type'], 
            $_POST['duration'],
            $_POST['days'],
            $_POST['nights'],
            $features,
            $inclusions,
            $_POST['status'], 
            $id
        ];
    } else {
        $sql = "UPDATE tour_packages SET
            destination_id=?, title=?, description=?, locations_covered=?, price=?, price_type=?, min_people=?, package_type=?,
            duration=?, days=?, nights=?, features=?, inclusions=?, status=? WHERE id=?";
        $data = [
            $_POST['destination_id'],
            $_POST['title'], 
            $_POST['description'],
            $locations_covered,
            $_POST['price'],
            $_POST['price_type'],
            $_POST['min_people'],
            $_POST['type'], 
            $_POST['duration'],
            $_POST['days'],
            $_POST['nights'],
            $features,
            $inclusions,
            $_POST['status'], 
            $id
        ];
    }

    $pdo->prepare($sql)->execute($data);
    header("Location: admin_pannel.php?section=packages&success=Package updated successfully");
    exit;
}

// Fetch packages with destination info
$packages = $pdo->query("
    SELECT tp.*, pd.title as destination_name 
    FROM tour_packages tp 
    LEFT JOIN popular_destinations pd ON tp.destination_id = pd.id 
    ORDER BY tp.id DESC
")->fetchAll();

/* ===============================
   HOTELS OPERATIONS
================================ */
if (isset($_POST['add_hotel'])) {
    $img = time() . $_FILES['hotel_image']['name'];
    move_uploaded_file($_FILES['hotel_image']['tmp_name'], "../uploads/" . $img);

    // ✅ FEATURES AS TEXT
    $features = isset($_POST['features'])
        ? implode(', ', array_map('trim', $_POST['features']))
        : '';

    $stmt = $pdo->prepare("
        INSERT INTO hotels
        (destination_id, hotel_name, description, price_per_night, image, category, features, status)
        VALUES (?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $_POST['destination_id'],
        $_POST['hotel_name'],
        $_POST['description'],
        $_POST['price'],
        $img,
        $_POST['category'],
        $features,
        $_POST['status']
    ]);

    header("Location: admin_pannel.php?section=hotels&success=Hotel added successfully");
    exit;
}

if (isset($_GET['delete_hotel'])) {
    $pdo->prepare("DELETE FROM hotels WHERE id=?")->execute([$_GET['delete_hotel']]);
    header("Location: admin_pannel.php?section=hotels&success=Hotel deleted successfully");
    exit;
}

if (isset($_POST['update_hotel'])) {
    $id = $_POST['id'];

    // ✅ FEATURES AS TEXT
    $features = isset($_POST['features'])
        ? implode(', ', array_map('trim', $_POST['features']))
        : '';

    if (!empty($_FILES['hotel_image']['name'])) {
        $img = time() . $_FILES['hotel_image']['name'];
        move_uploaded_file($_FILES['hotel_image']['tmp_name'], "../uploads/" . $img);

        $sql = "UPDATE hotels SET
            destination_id=?, hotel_name=?, description=?, price_per_night=?, image=?,
            category=?, features=?, status=? WHERE id=?";
        $data = [
            $_POST['destination_id'],
            $_POST['hotel_name'], $_POST['description'], $_POST['price'],
            $img, $_POST['category'], $features, $_POST['status'], $id
        ];
    } else {
        $sql = "UPDATE hotels SET
            destination_id=?, hotel_name=?, description=?, price_per_night=?,
            category=?, features=?, status=? WHERE id=?";
        $data = [
            $_POST['destination_id'],
            $_POST['hotel_name'], $_POST['description'], $_POST['price'],
            $_POST['category'], $features, $_POST['status'], $id
        ];
    }

    $pdo->prepare($sql)->execute($data);
    header("Location: admin_pannel.php?section=hotels&success=Hotel updated successfully");
    exit;
}

// Fetch hotels with destination info
$hotels = $pdo->query("
    SELECT h.*, pd.title as destination_name 
    FROM hotels h 
    LEFT JOIN popular_destinations pd ON h.destination_id = pd.id 
    ORDER BY h.id DESC
")->fetchAll();

/* ===============================
   HERO SECTION OPERATIONS
================================ */
// Handle hero content update
if (isset($_POST['update_hero_content'])) {
    $stmt = $pdo->prepare("
        UPDATE hero_content SET 
        main_title = ?, 
        main_description = ?, 
        button_text = ?, 
        button_link = ?,
        is_active = ? 
        WHERE id = 1
    ");
    
    $stmt->execute([
        $_POST['main_title'],
        $_POST['main_description'],
        $_POST['button_text'],
        $_POST['button_link'],
        $_POST['is_active']
    ]);
    
    header("Location: admin_pannel.php?section=hero&success=Hero content updated successfully");
    exit;
}

// Handle carousel image upload
if (isset($_POST['add_carousel_image'])) {
    $image_name = time() . '_' . $_FILES['carousel_image']['name'];
    $thumbnail_name = 'thumb_' . time() . '_' . $_FILES['thumbnail_image']['name'];
    
    $image_path = "../uploads/hero/" . $image_name;
    $thumbnail_path = "../uploads/hero/thumbnails/" . $thumbnail_name;
    
    // Create directories if they don't exist
    if (!is_dir('../uploads/hero')) {
        mkdir('../uploads/hero', 0777, true);
    }
    if (!is_dir('../uploads/hero/thumbnails')) {
        mkdir('../uploads/hero/thumbnails', 0777, true);
    }
    
    // Move uploaded files
    move_uploaded_file($_FILES['carousel_image']['tmp_name'], $image_path);
    move_uploaded_file($_FILES['thumbnail_image']['tmp_name'], $thumbnail_path);
    
    $stmt = $pdo->prepare("
        INSERT INTO hero_carousel 
        (image_url, thumbnail_url, alt_text, title, description, display_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'uploads/hero/' . $image_name,
        'uploads/hero/thumbnails/' . $thumbnail_name,
        $_POST['alt_text'],
        $_POST['title'],
        $_POST['description'],
        $_POST['display_order'],
        $_POST['is_active']
    ]);
    
    header("Location: admin_pannel.php?section=hero&success=Carousel image added successfully");
    exit;
}

// Handle carousel image update
if (isset($_POST['update_carousel_image'])) {
    $id = $_POST['id'];
    
    // Get existing image URLs
    $stmt = $pdo->prepare("SELECT image_url, thumbnail_url FROM hero_carousel WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    
    $image_url = $existing['image_url'];
    $thumbnail_url = $existing['thumbnail_url'];
    
    // Handle new main image upload
    if (!empty($_FILES['carousel_image']['name'])) {
        $image_name = time() . '_' . $_FILES['carousel_image']['name'];
        $image_path = "../uploads/hero/" . $image_name;
        move_uploaded_file($_FILES['carousel_image']['tmp_name'], $image_path);
        $image_url = 'uploads/hero/' . $image_name;
    }
    
    // Handle new thumbnail upload
    if (!empty($_FILES['thumbnail_image']['name'])) {
        $thumbnail_name = 'thumb_' . time() . '_' . $_FILES['thumbnail_image']['name'];
        $thumbnail_path = "../uploads/hero/thumbnails/" . $thumbnail_name;
        move_uploaded_file($_FILES['thumbnail_image']['tmp_name'], $thumbnail_path);
        $thumbnail_url = 'uploads/hero/thumbnails/' . $thumbnail_name;
    }
    
    $sql = "UPDATE hero_carousel SET 
            image_url = ?,
            thumbnail_url = ?,
            alt_text = ?, 
            title = ?, 
            description = ?, 
            display_order = ?, 
            is_active = ? 
            WHERE id = ?";
    
    $pdo->prepare($sql)->execute([
        $image_url,
        $thumbnail_url,
        $_POST['alt_text'],
        $_POST['title'],
        $_POST['description'],
        $_POST['display_order'],
        $_POST['is_active'],
        $id
    ]);
    
    header("Location: admin_pannel.php?section=hero&success=Carousel image updated successfully");
    exit;
}

// Handle carousel image deletion
if (isset($_GET['delete_carousel'])) {
    // Get image paths to delete files
    $stmt = $pdo->prepare("SELECT image_url, thumbnail_url FROM hero_carousel WHERE id = ?");
    $stmt->execute([$_GET['delete_carousel']]);
    $images = $stmt->fetch();
    
    if ($images) {
        // Delete files from server
        if (file_exists("../" . $images['image_url'])) {
            unlink("../" . $images['image_url']);
        }
        if (file_exists("../" . $images['thumbnail_url'])) {
            unlink("../" . $images['thumbnail_url']);
        }
    }
    
    $pdo->prepare("DELETE FROM hero_carousel WHERE id = ?")->execute([$_GET['delete_carousel']]);
    header("Location: admin_pannel.php?section=hero&success=Carousel image deleted successfully");
    exit;
}

// Fetch hero content
$hero_content = $pdo->query("SELECT * FROM hero_content LIMIT 1")->fetch();
if (!$hero_content) {
    // Insert default if not exists
    $pdo->query("INSERT INTO hero_content (main_title, main_description, button_text, button_link, is_active) 
                 VALUES ('Discover Amazing Destinations', 'Your dream vacation is just a click away!', 'Explore Now', '#packages', 1)");
    $hero_content = $pdo->query("SELECT * FROM hero_content LIMIT 1")->fetch();
}

// Fetch carousel images
$carousel_images = $pdo->query("SELECT * FROM hero_carousel ORDER BY display_order, created_at DESC")->fetchAll();

/* ===============================
   POPULAR DESTINATIONS OPERATIONS
================================ */
// Fetch count for sidebar
$destinationCount = $pdo->query("SELECT COUNT(*) FROM popular_destinations")->fetchColumn();

if (isset($_POST['add_destination'])) {
    $img = time() . '_' . $_FILES['image']['name'];
    $upload_path = "../uploads/" . $img;
    
    // Create directory if it doesn't exist
    if (!is_dir('../uploads/')) {
        mkdir('../uploads/', 0777, true);
    }
    
    move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
    
    // Generate slug from title if not provided
    $slug = $_POST['slug'];
    if (empty($slug) && !empty($_POST['title'])) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title']), '-'));
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO popular_destinations
        (title, slug, image, status, display_order)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['title'],
        $slug,
        $img,
        $_POST['status'],
        $_POST['display_order']
    ]);
    
    header("Location: admin_pannel.php?section=destinations&success=Destination added successfully");
    exit;
}

if (isset($_GET['delete_destination'])) {
    // Optional: Delete the image file from server
    $stmt = $pdo->prepare("SELECT image FROM popular_destinations WHERE id = ?");
    $stmt->execute([$_GET['delete_destination']]);
    $image = $stmt->fetchColumn();
    
    if ($image && file_exists("../uploads/" . $image)) {
        unlink("../uploads/" . $image);
    }
    
    $pdo->prepare("DELETE FROM popular_destinations WHERE id = ?")->execute([$_GET['delete_destination']]);
    header("Location: admin_pannel.php?section=destinations&success=Destination deleted successfully");
    exit;
}

if (isset($_POST['update_destination'])) {
    $id = $_POST['id'];
    
    // Generate slug from title if not provided
    $slug = $_POST['slug'];
    if (empty($slug) && !empty($_POST['title'])) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title']), '-'));
    }
    
    if (!empty($_FILES['image']['name'])) {
        // Upload new image
        $img = time() . '_' . $_FILES['image']['name'];
        $upload_path = "../uploads/" . $img;
        
        // Delete old image
        $stmt = $pdo->prepare("SELECT image FROM popular_destinations WHERE id = ?");
        $stmt->execute([$id]);
        $old_image = $stmt->fetchColumn();
        
        if ($old_image && file_exists("../uploads/" . $old_image)) {
            unlink("../uploads/" . $old_image);
        }
        
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
        
        $sql = "UPDATE popular_destinations SET 
                title = ?, slug = ?, image = ?, status = ?, display_order = ? 
                WHERE id = ?";
        $data = [
            $_POST['title'],
            $slug,
            $img,
            $_POST['status'],
            $_POST['display_order'],
            $id
        ];
    } else {
        $sql = "UPDATE popular_destinations SET 
                title = ?, slug = ?, status = ?, display_order = ? 
                WHERE id = ?";
        $data = [
            $_POST['title'],
            $slug,
            $_POST['status'],
            $_POST['display_order'],
            $id
        ];
    }
    
    $pdo->prepare($sql)->execute($data);
    header("Location: admin_pannel.php?section=destinations&success=Destination updated successfully");
    exit;
}

// Fetch destinations
$destinations = $pdo->query("SELECT * FROM popular_destinations ORDER BY display_order, created_at DESC")->fetchAll();

// Fetch active destinations for dropdowns
$active_destinations = $pdo->query("
    SELECT id, title FROM popular_destinations 
    WHERE status = 'Active' 
    ORDER BY display_order, title
")->fetchAll();

/* ===============================
   ENQUIRIES MANAGEMENT
================================ */
// Handle enquiry status update
if (isset($_POST['update_enquiry_status'])) {
    $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header("Location: admin_pannel.php?section=enquiries&success=Enquiry status updated successfully");
    exit;
}

// Handle enquiry deletion
if (isset($_GET['delete_enquiry'])) {
    $pdo->prepare("DELETE FROM enquiries WHERE id = ?")->execute([$_GET['delete_enquiry']]);
    header("Location: admin_pannel.php?section=enquiries&success=Enquiry deleted successfully");
    exit;
}

// Handle bulk action (mark as read)
if (isset($_POST['bulk_action']) && isset($_POST['selected_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['selected_ids']));
    
    if ($_POST['bulk_action'] == 'mark_read') {
        $pdo->query("UPDATE enquiries SET status = 'Read' WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=enquiries&success=Selected enquiries marked as read");
    } elseif ($_POST['bulk_action'] == 'mark_in_progress') {
        $pdo->query("UPDATE enquiries SET status = 'In Progress' WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=enquiries&success=Selected enquiries marked as in progress");
    } elseif ($_POST['bulk_action'] == 'delete_selected') {
        $pdo->query("DELETE FROM enquiries WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=enquiries&success=Selected enquiries deleted");
    }
    exit;
}

// Fetch enquiries with optional filtering
$enquiry_filter = $_GET['enquiry_filter'] ?? 'all';
$enquiry_status = $_GET['enquiry_status'] ?? '';
$enquiry_source = $_GET['enquiry_source'] ?? '';

$enquiry_query = "SELECT e.*, tp.title as package_title FROM enquiries e 
                  LEFT JOIN tour_packages tp ON e.package_id = tp.id WHERE 1=1";
$params = [];

if (!empty($enquiry_status)) {
    $enquiry_query .= " AND e.status = ?";
    $params[] = $enquiry_status;
}

if (!empty($enquiry_source)) {
    $enquiry_query .= " AND e.source = ?";
    $params[] = $enquiry_source;
}

$enquiry_query .= " ORDER BY e.created_at DESC";

$stmt = $pdo->prepare($enquiry_query);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

// Enquiry counts
$total_enquiries = $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$new_enquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();
$read_enquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Read'")->fetchColumn();
$in_progress_enquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'In Progress'")->fetchColumn();
$closed_enquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Closed'")->fetchColumn();

// Fetch unique sources for filter
$sources = $pdo->query("SELECT DISTINCT source FROM enquiries WHERE source IS NOT NULL ORDER BY source")->fetchAll(PDO::FETCH_COLUMN);

/* ===============================
   OTHER SERVICES ENQUIRIES
================================ */
// Handle other service enquiry status update
if (isset($_POST['update_other_service_status'])) {
    $stmt = $pdo->prepare("UPDATE other_service SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header("Location: admin_pannel.php?section=other_services&success=Service enquiry status updated successfully");
    exit;
}

// Handle other service enquiry deletion
if (isset($_GET['delete_other_service'])) {
    $pdo->prepare("DELETE FROM other_service WHERE id = ?")->execute([$_GET['delete_other_service']]);
    header("Location: admin_pannel.php?section=other_services&success=Service enquiry deleted successfully");
    exit;
}

// Fetch other service enquiries
$other_services = $pdo->query("SELECT * FROM other_service ORDER BY created_at DESC")->fetchAll();

// Other service counts
$total_other_services = $pdo->query("SELECT COUNT(*) FROM other_service")->fetchColumn();
$new_other_services = $pdo->query("SELECT COUNT(*) FROM other_service WHERE status = 'New'")->fetchColumn();

// Dashboard counts
$packageCount = $pdo->query("SELECT COUNT(*) FROM tour_packages")->fetchColumn();
$hotelCount   = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
$carouselCount = $pdo->query("SELECT COUNT(*) FROM hero_carousel")->fetchColumn();
$activeCarouselCount = $pdo->query("SELECT COUNT(*) FROM hero_carousel WHERE is_active = 1")->fetchColumn();

/* ===============================
   HOTEL ENQUIRIES MANAGEMENT
================================ */
// Check if hotel_enquiries table exists
try {
    $pdo->query("SELECT 1 FROM hotel_enquiries LIMIT 1");
} catch (PDOException $e) {
    // Create hotel_enquiries table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `hotel_enquiries` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `hotel_id` int(11) DEFAULT NULL,
            `full_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `phone` varchar(50) NOT NULL,
            `check_in_date` date DEFAULT NULL,
            `check_out_date` date DEFAULT NULL,
            `guests` varchar(50) DEFAULT NULL,
            `rooms` int(11) DEFAULT 1,
            `message` text,
            `status` enum('New','Read','In Progress','Closed','Cancelled') DEFAULT 'New',
            `source` varchar(50) DEFAULT 'website',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `hotel_id` (`hotel_id`),
            CONSTRAINT `hotel_enquiries_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Fetch hotel enquiries with optional filtering
$hotel_filter = $_GET['hotel_filter'] ?? '';
$hotel_status_filter = $_GET['hotel_status_filter'] ?? '';
$hotel_date_from = $_GET['hotel_date_from'] ?? '';
$hotel_date_to = $_GET['hotel_date_to'] ?? '';

$hotel_enquiry_query = "SELECT he.*, h.hotel_name, h.category, h.price_per_night, pd.title as destination_name 
                        FROM hotel_enquiries he 
                        LEFT JOIN hotels h ON he.hotel_id = h.id 
                        LEFT JOIN popular_destinations pd ON h.destination_id = pd.id 
                        WHERE 1=1";
$params = [];

if (!empty($hotel_filter)) {
    $hotel_enquiry_query .= " AND he.hotel_id = ?";
    $params[] = $hotel_filter;
}

if (!empty($hotel_status_filter)) {
    $hotel_enquiry_query .= " AND he.status = ?";
    $params[] = $hotel_status_filter;
}

if (!empty($hotel_date_from)) {
    $hotel_enquiry_query .= " AND DATE(he.created_at) >= ?";
    $params[] = $hotel_date_from;
}

if (!empty($hotel_date_to)) {
    $hotel_enquiry_query .= " AND DATE(he.created_at) <= ?";
    $params[] = $hotel_date_to;
}

$hotel_enquiry_query .= " ORDER BY he.created_at DESC";

$stmt = $pdo->prepare($hotel_enquiry_query);
$stmt->execute($params);
$hotel_enquiries = $stmt->fetchAll();

// Hotel enquiry counts
$total_hotel_enquiries = $pdo->query("SELECT COUNT(*) FROM hotel_enquiries")->fetchColumn();
$new_hotel_enquiries = $pdo->query("SELECT COUNT(*) FROM hotel_enquiries WHERE status = 'New'")->fetchColumn();
$read_hotel_enquiries = $pdo->query("SELECT COUNT(*) FROM hotel_enquiries WHERE status = 'Read'")->fetchColumn();
$in_progress_hotel_enquiries = $pdo->query("SELECT COUNT(*) FROM hotel_enquiries WHERE status = 'In Progress'")->fetchColumn();
$closed_hotel_enquiries = $pdo->query("SELECT COUNT(*) FROM hotel_enquiries WHERE status = 'Closed'")->fetchColumn();
$cancelled_hotel_enquiries = $pdo->query("SELECT COUNT(*) FROM hotel_enquiries WHERE status = 'Cancelled'")->fetchColumn();

// Fetch all hotels for filter dropdown
$all_hotels = $pdo->query("SELECT id, hotel_name FROM hotels ORDER BY hotel_name")->fetchAll();

// Handle hotel enquiry status update
if (isset($_POST['update_hotel_enquiry_status'])) {
    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        
        // Validate status value
        $valid_statuses = ['New', 'Read', 'In Progress', 'Closed', 'Cancelled'];
        if (in_array($status, $valid_statuses)) {
            $stmt = $pdo->prepare("UPDATE hotel_enquiries SET status = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$status, $id]);
            
            if ($result) {
                header("Location: admin_pannel.php?section=hotel_enquiries&success=Hotel enquiry status updated successfully");
                exit;
            } else {
                header("Location: admin_pannel.php?section=hotel_enquiries&error=Failed to update status");
                exit;
            }
        } else {
            header("Location: admin_pannel.php?section=hotel_enquiries&error=Invalid status value");
            exit;
        }
    } else {
        header("Location: admin_pannel.php?section=hotel_enquiries&error=Missing parameters");
        exit;
    }
}

// Handle hotel enquiry deletion
if (isset($_GET['delete_hotel_enquiry'])) {
    $id = intval($_GET['delete_hotel_enquiry']);
    $pdo->prepare("DELETE FROM hotel_enquiries WHERE id = ?")->execute([$id]);
    header("Location: admin_pannel.php?section=hotel_enquiries&success=Hotel enquiry deleted successfully");
    exit;
}

// Handle bulk actions for hotel enquiries
if (isset($_POST['bulk_hotel_action']) && isset($_POST['selected_hotel_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['selected_hotel_ids']));
    
    if ($_POST['bulk_hotel_action'] == 'mark_read') {
        $pdo->query("UPDATE hotel_enquiries SET status = 'Read', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=hotel_enquiries&success=Selected enquiries marked as read");
    } elseif ($_POST['bulk_hotel_action'] == 'mark_in_progress') {
        $pdo->query("UPDATE hotel_enquiries SET status = 'In Progress', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=hotel_enquiries&success=Selected enquiries marked as in progress");
    } elseif ($_POST['bulk_hotel_action'] == 'mark_closed') {
        $pdo->query("UPDATE hotel_enquiries SET status = 'Closed', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=hotel_enquiries&success=Selected enquiries marked as closed");
    } elseif ($_POST['bulk_hotel_action'] == 'delete_selected') {
        $pdo->query("DELETE FROM hotel_enquiries WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=hotel_enquiries&success=Selected enquiries deleted");
    }
    exit;
}

// Handle export to Excel/CSV
if (isset($_POST['export_hotel_enquiries'])) {
    // Get filter parameters
    $hotel_filter_export = $_POST['hotel_filter_export'] ?? '';
    $hotel_status_filter_export = $_POST['hotel_status_filter_export'] ?? '';
    $hotel_date_from_export = $_POST['hotel_date_from_export'] ?? '';
    $hotel_date_to_export = $_POST['hotel_date_to_export'] ?? '';
    
    $query = "SELECT he.*, h.hotel_name, h.category, h.price_per_night, pd.title as destination_name 
              FROM hotel_enquiries he 
              LEFT JOIN hotels h ON he.hotel_id = h.id 
              LEFT JOIN popular_destinations pd ON h.destination_id = pd.id 
              WHERE 1=1";
    $params = [];
    
    if (!empty($hotel_filter_export)) {
        $query .= " AND he.hotel_id = ?";
        $params[] = $hotel_filter_export;
    }
    
    if (!empty($hotel_status_filter_export)) {
        $query .= " AND he.status = ?";
        $params[] = $hotel_status_filter_export;
    }
    
    if (!empty($hotel_date_from_export)) {
        $query .= " AND DATE(he.created_at) >= ?";
        $params[] = $hotel_date_from_export;
    }
    
    if (!empty($hotel_date_to_export)) {
        $query .= " AND DATE(he.created_at) <= ?";
        $params[] = $hotel_date_to_export;
    }
    
    $query .= " ORDER BY he.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $export_data = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=hotel_enquiries_' . date('Y-m-d_H-i-s') . '.csv');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    fputcsv($output, [
        'ID', 'Hotel Name', 'Destination', 'Category', 'Guest Name', 'Email', 'Phone', 
        'Check-In Date', 'Check-Out Date', 'Guests', 'Rooms', 'Message',
        'Status', 'Source', 'Submitted Date', 'Last Updated'
    ]);
    
    // Add data rows
    foreach ($export_data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['hotel_name'] ?? 'N/A',
            $row['destination_name'] ?? 'N/A',
            $row['category'] ?? 'N/A',
            $row['full_name'],
            $row['email'],
            $row['phone'],
            $row['check_in_date'] ?? 'N/A',
            $row['check_out_date'] ?? 'N/A',
            $row['guests'] ?? '1',
            $row['rooms'] ?? '1',
            $row['message'] ?? '',
            $row['status'],
            $row['source'] ?? 'website',
            $row['created_at'],
            $row['updated_at'] ?? $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}
/* ===============================
   CONTACT MESSAGES MANAGEMENT
================================ */
// Fetch contact messages with optional filtering
$contact_filter_status = $_GET['contact_filter_status'] ?? '';
$contact_date_from = $_GET['contact_date_from'] ?? '';
$contact_date_to = $_GET['contact_date_to'] ?? '';

$contact_query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if (!empty($contact_filter_status)) {
    $contact_query .= " AND status = ?";
    $params[] = $contact_filter_status;
}

if (!empty($contact_date_from)) {
    $contact_query .= " AND DATE(created_at) >= ?";
    $params[] = $contact_date_from;
}

if (!empty($contact_date_to)) {
    $contact_query .= " AND DATE(created_at) <= ?";
    $params[] = $contact_date_to;
}

$contact_query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($contact_query);
$stmt->execute($params);
$contact_messages = $stmt->fetchAll();

// Contact message counts
$total_contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$new_contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'New'")->fetchColumn();
$read_contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Read'")->fetchColumn();
$replied_contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Replied'")->fetchColumn();
$archived_contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Archived'")->fetchColumn();

// Handle contact message status update
if (isset($_POST['update_contact_status'])) {
    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        
        // Validate status value
        $valid_statuses = ['New', 'Read', 'Replied', 'Archived'];
        if (in_array($status, $valid_statuses)) {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$status, $id]);
            
            if ($result) {
                header("Location: admin_pannel.php?section=contact_messages&success=Contact message status updated successfully");
                exit;
            } else {
                header("Location: admin_pannel.php?section=contact_messages&error=Failed to update status");
                exit;
            }
        } else {
            header("Location: admin_pannel.php?section=contact_messages&error=Invalid status value");
            exit;
        }
    } else {
        header("Location: admin_pannel.php?section=contact_messages&error=Missing parameters");
        exit;
    }
}

// Handle contact message deletion
if (isset($_GET['delete_contact'])) {
    $id = intval($_GET['delete_contact']);
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
    header("Location: admin_pannel.php?section=contact_messages&success=Contact message deleted successfully");
    exit;
}

// Handle bulk actions for contact messages
if (isset($_POST['bulk_contact_action']) && isset($_POST['selected_contact_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['selected_contact_ids']));
    
    if ($_POST['bulk_contact_action'] == 'mark_read') {
        $pdo->query("UPDATE contact_messages SET status = 'Read', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=contact_messages&success=Selected messages marked as read");
    } elseif ($_POST['bulk_contact_action'] == 'mark_replied') {
        $pdo->query("UPDATE contact_messages SET status = 'Replied', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=contact_messages&success=Selected messages marked as replied");
    } elseif ($_POST['bulk_contact_action'] == 'mark_archived') {
        $pdo->query("UPDATE contact_messages SET status = 'Archived', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=contact_messages&success=Selected messages archived");
    } elseif ($_POST['bulk_contact_action'] == 'delete_selected') {
        $pdo->query("DELETE FROM contact_messages WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=contact_messages&success=Selected messages deleted");
    }
    exit;
}

// Handle export to CSV
if (isset($_POST['export_contact_messages'])) {
    // Get filter parameters
    $contact_filter_status_export = $_POST['contact_filter_status_export'] ?? '';
    $contact_date_from_export = $_POST['contact_date_from_export'] ?? '';
    $contact_date_to_export = $_POST['contact_date_to_export'] ?? '';
    
    $query = "SELECT * FROM contact_messages WHERE 1=1";
    $params = [];
    
    if (!empty($contact_filter_status_export)) {
        $query .= " AND status = ?";
        $params[] = $contact_filter_status_export;
    }
    
    if (!empty($contact_date_from_export)) {
        $query .= " AND DATE(created_at) >= ?";
        $params[] = $contact_date_from_export;
    }
    
    if (!empty($contact_date_to_export)) {
        $query .= " AND DATE(created_at) <= ?";
        $params[] = $contact_date_to_export;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $export_data = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=contact_messages_' . date('Y-m-d_H-i-s') . '.csv');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    fputcsv($output, [
        'ID', 'Full Name', 'Email', 'Phone', 'Subject', 'Message', 
        'Status', 'IP Address', 'User Agent', 'Created At', 'Updated At'
    ]);
    
    // Add data rows
    foreach ($export_data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['full_name'],
            $row['email'],
            $row['phone'] ?? '',
            $row['subject'] ?? '',
            $row['message'],
            $row['status'],
            $row['ip_address'] ?? '',
            $row['user_agent'] ?? '',
            $row['created_at'],
            $row['updated_at'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}
/* ===============================
   FEEDBACKS MANAGEMENT
================================ */
// Fetch feedbacks with optional filtering
$feedback_filter_status = $_GET['feedback_filter_status'] ?? '';
$feedback_filter_rating = $_GET['feedback_filter_rating'] ?? '';
$feedback_date_from = $_GET['feedback_date_from'] ?? '';
$feedback_date_to = $_GET['feedback_date_to'] ?? '';
$feedback_search = $_GET['feedback_search'] ?? '';

$feedback_query = "SELECT * FROM feedback WHERE 1=1";
$params = [];

if (!empty($feedback_filter_status)) {
    $feedback_query .= " AND status = ?";
    $params[] = $feedback_filter_status;
}

if (!empty($feedback_filter_rating)) {
    $feedback_query .= " AND rating = ?";
    $params[] = $feedback_filter_rating;
}

if (!empty($feedback_date_from)) {
    $feedback_query .= " AND DATE(created_at) >= ?";
    $params[] = $feedback_date_from;
}

if (!empty($feedback_date_to)) {
    $feedback_query .= " AND DATE(created_at) <= ?";
    $params[] = $feedback_date_to;
}

if (!empty($feedback_search)) {
    $feedback_query .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ? OR subject LIKE ?)";
    $search_term = "%$feedback_search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$feedback_query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($feedback_query);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

// Feedback counts
$total_feedbacks = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$new_feedbacks = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'New'")->fetchColumn();
$published_feedbacks = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Published'")->fetchColumn();
$pending_feedbacks = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Pending'")->fetchColumn();
$rejected_feedbacks = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Rejected'")->fetchColumn();
$archived_feedbacks = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Archived'")->fetchColumn();

// Rating distribution
$rating_counts = [];
for ($i = 1; $i <= 5; $i++) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM feedback WHERE rating = ?");
    $stmt->execute([$i]);
    $rating_counts[$i] = $stmt->fetchColumn();
}
$average_rating = $total_feedbacks > 0 
    ? round($pdo->query("SELECT AVG(rating) FROM feedback WHERE rating IS NOT NULL")->fetchColumn(), 1) 
    : 0;

// Handle feedback status update
if (isset($_POST['update_feedback_status'])) {
    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        
        $valid_statuses = ['New', 'Published', 'Pending', 'Rejected', 'Archived'];
        if (in_array($status, $valid_statuses)) {
            $stmt = $pdo->prepare("UPDATE feedback SET status = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$status, $id]);
            
            if ($result) {
                header("Location: admin_pannel.php?section=feedbacks&success=Feedback status updated successfully");
                exit;
            } else {
                header("Location: admin_pannel.php?section=feedbacks&error=Failed to update status");
                exit;
            }
        } else {
            header("Location: admin_pannel.php?section=feedbacks&error=Invalid status value");
            exit;
        }
    } else {
        header("Location: admin_pannel.php?section=feedbacks&error=Missing parameters");
        exit;
    }
}

// Handle admin notes update
if (isset($_POST['update_feedback_notes'])) {
    if (isset($_POST['id']) && isset($_POST['admin_notes'])) {
        $id = intval($_POST['id']);
        $admin_notes = $_POST['admin_notes'];
        
        $stmt = $pdo->prepare("UPDATE feedback SET admin_notes = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$admin_notes, $id]);
        
        if ($result) {
            header("Location: admin_pannel.php?section=feedbacks&success=Admin notes updated successfully");
            exit;
        } else {
            header("Location: admin_pannel.php?section=feedbacks&error=Failed to update notes");
            exit;
        }
    } else {
        header("Location: admin_pannel.php?section=feedbacks&error=Missing parameters");
        exit;
    }
}

// Handle feedback deletion
if (isset($_GET['delete_feedback'])) {
    $id = intval($_GET['delete_feedback']);
    $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
    header("Location: admin_pannel.php?section=feedbacks&success=Feedback deleted successfully");
    exit;
}

// Handle bulk actions for feedbacks
if (isset($_POST['bulk_feedback_action']) && isset($_POST['selected_feedback_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['selected_feedback_ids']));
    
    if ($_POST['bulk_feedback_action'] == 'publish') {
        $pdo->query("UPDATE feedback SET status = 'Published', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=feedbacks&success=Selected feedbacks published");
    } elseif ($_POST['bulk_feedback_action'] == 'pending') {
        $pdo->query("UPDATE feedback SET status = 'Pending', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=feedbacks&success=Selected feedbacks marked as pending");
    } elseif ($_POST['bulk_feedback_action'] == 'reject') {
        $pdo->query("UPDATE feedback SET status = 'Rejected', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=feedbacks&success=Selected feedbacks rejected");
    } elseif ($_POST['bulk_feedback_action'] == 'archive') {
        $pdo->query("UPDATE feedback SET status = 'Archived', updated_at = NOW() WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=feedbacks&success=Selected feedbacks archived");
    } elseif ($_POST['bulk_feedback_action'] == 'delete_selected') {
        $pdo->query("DELETE FROM feedback WHERE id IN ($ids)");
        header("Location: admin_pannel.php?section=feedbacks&success=Selected feedbacks deleted");
    }
    exit;
}

// Handle export to CSV
if (isset($_POST['export_feedbacks'])) {
    $feedback_filter_status_export = $_POST['feedback_filter_status_export'] ?? '';
    $feedback_filter_rating_export = $_POST['feedback_filter_rating_export'] ?? '';
    $feedback_date_from_export = $_POST['feedback_date_from_export'] ?? '';
    $feedback_date_to_export = $_POST['feedback_date_to_export'] ?? '';
    
    $query = "SELECT * FROM feedback WHERE 1=1";
    $params = [];
    
    if (!empty($feedback_filter_status_export)) {
        $query .= " AND status = ?";
        $params[] = $feedback_filter_status_export;
    }
    
    if (!empty($feedback_filter_rating_export)) {
        $query .= " AND rating = ?";
        $params[] = $feedback_filter_rating_export;
    }
    
    if (!empty($feedback_date_from_export)) {
        $query .= " AND DATE(created_at) >= ?";
        $params[] = $feedback_date_from_export;
    }
    
    if (!empty($feedback_date_to_export)) {
        $query .= " AND DATE(created_at) <= ?";
        $params[] = $feedback_date_to_export;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $export_data = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=feedbacks_' . date('Y-m-d_H-i-s') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, [
        'ID', 'Name', 'Email', 'Phone', 'Subject', 'Message', 
        'Rating', 'Status', 'Admin Notes', 'IP Address', 'Page URL',
        'Created At', 'Updated At'
    ]);
    
    foreach ($export_data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'] ?? '',
            $row['subject'] ?? '',
            $row['message'],
            $row['rating'] ? $row['rating'] . ' Star' : 'Not Rated',
            $row['status'],
            $row['admin_notes'] ?? '',
            $row['ip_address'] ?? '',
            $row['page_url'] ?? '',
            $row['created_at'],
            $row['updated_at'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExploreWorld - Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --main-orange: #F28C28;
            --dark-orange: #E56B1F;
            --light-orange: #F7A541;
            --red-border: #C0392B;
            --dark-brown: #3A2506;
            --sky-blue: #08A6C2;
            --black: #000000;
            --white: #FFFFFF;
            --sidebar-width: 260px;
            --gray-light: #f5f7fa;
            --gray-border: rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 5px 15px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-light);
            color: #333;
            overflow-x: hidden;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark-brown);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--main-orange);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-header h3 i {
            font-size: 1.8rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(242, 140, 40, 0.1);
            color: white;
            border-left-color: var(--main-orange);
        }
        
        .sidebar-menu a i {
            width: 25px;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .sidebar-menu .badge {
            margin-left: auto;
            background-color: var(--main-orange) !important;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
        }
        
        .top-nav {
            background-color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-nav-left {
            display: flex;
            align-items: center;
        }
        
        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-brown);
            margin-right: 15px;
            display: none;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .menu-toggle:hover {
            color: var(--main-orange);
        }
        
        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-brown);
        }
        
        .page-title p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .admin-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--main-orange);
            transition: var(--transition);
        }
        
        .admin-profile img:hover {
            transform: scale(1.05);
            border-color: var(--dark-orange);
        }
        
        .content-area {
            padding: 25px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            transition: var(--transition);
            border-left: 5px solid var(--main-orange);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-1 .stat-icon { background-color: var(--main-orange); }
        .stat-2 .stat-icon { background-color: var(--sky-blue); }
        .stat-3 .stat-icon { background-color: #6c5ce7; }
        .stat-4 .stat-icon { background-color: #00b894; }
        .stat-5 .stat-icon { background-color: #9b59b6; }
        .stat-6 .stat-icon { background-color: #e74c3c; }
        
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--gray-border);
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-brown);
        }
        
        .card-header h5 i {
            color: var(--main-orange);
            margin-right: 8px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .btn-add {
            background-color: var(--main-orange);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }
        
        .btn-add:hover {
            background-color: var(--dark-orange);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-submit {
            background-color: var(--main-orange);
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            border: none;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .btn-submit:hover {
            background-color: var(--dark-orange);
            transform: translateY(-2px);
        }
        
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 4px;
            transition: var(--transition);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
            padding: 0 15px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid var(--gray-border);
            color: var(--dark-brown);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .table img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            transition: var(--transition);
        }
        
        .table img:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-sm);
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge {
            padding: 5px 10px;
            font-weight: 500;
        }
        
        .feature-badge {
            display: inline-block;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 15px;
            margin: 2px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .price-badge {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .nights-badge {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            color: #856404;
        }
        
        .enquiry-detail-label {
            font-weight: 600;
            color: var(--dark-brown);
            margin-bottom: 5px;
        }
        
        .enquiry-detail-value {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            word-break: break-word;
        }
        
        .small-card {
            font-size: 0.9rem;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .small-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .source-badge {
            background-color: #6c5ce7;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: var(--shadow-sm);
            animation: slideInDown 0.5s ease;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .modal-content {
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .modal-header {
            background-color: var(--dark-brown);
            color: white;
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-cogs"></i> Admin Panel</h3>
            </div>
            
            <div class="sidebar-menu">
                <ul>
                    <li>
                        <a href="?section=dashboard" class="<?= $section == 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="?section=packages" class="<?= $section == 'packages' ? 'active' : '' ?>">
                            <i class="fas fa-suitcase-rolling"></i> Tour Packages
                            <span class="badge rounded-pill"><?= $packageCount ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=hotels" class="<?= $section == 'hotels' ? 'active' : '' ?>">
                            <i class="fas fa-hotel"></i> Hotels
                            <span class="badge rounded-pill"><?= $hotelCount ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=hero" class="<?= $section == 'hero' ? 'active' : '' ?>">
                            <i class="fas fa-images"></i> Hero Section
                            <span class="badge rounded-pill"><?= $activeCarouselCount ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=destinations" class="<?= $section == 'destinations' ? 'active' : '' ?>">
                            <i class="fas fa-map-marker-alt"></i> Popular Destinations
                            <span class="badge rounded-pill"><?= $destinationCount ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=feedbacks" class="<?= $section == 'feedbacks' ? 'active' : '' ?>">
                            <i class="fas fa-star"></i> Feedbacks & Reviews
                            <span class="badge rounded-pill <?= $new_feedbacks > 0 ? 'bg-danger' : '' ?>"><?= $total_feedbacks ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=enquiries" class="<?= $section == 'enquiries' ? 'active' : '' ?>">
                            <i class="fas fa-envelope"></i> Enquiries
                            <span class="badge rounded-pill <?= $new_enquiries > 0 ? 'bg-danger' : '' ?>"><?= $total_enquiries ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=other_services" class="<?= $section == 'other_services' ? 'active' : '' ?>">
                            <i class="fas fa-concierge-bell"></i> Service Bookings
                            <span class="badge rounded-pill <?= $new_other_services > 0 ? 'bg-danger' : '' ?>"><?= $total_other_services ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=hotel_enquiries" class="<?= $section == 'hotel_enquiries' ? 'active' : '' ?>">
                            <i class="fas fa-bed"></i> Hotel Enquiries
                            <span class="badge rounded-pill <?= $new_hotel_enquiries > 0 ? 'bg-danger' : '' ?>"><?= $total_hotel_enquiries ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=contact_messages" class="<?= $section == 'contact_messages' ? 'active' : '' ?>">
                            <i class="fas fa-envelope-open-text"></i> Contact Messages
                            <span class="badge rounded-pill <?= $new_contacts > 0 ? 'bg-danger' : '' ?>"><?= $total_contacts ?></span>
                        </a>
                    </li>
                    <li>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                    </li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="top-nav-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-title">
                        <h1><?= ucfirst(str_replace('_', ' ', $section)) ?> Management</h1>
                        <p>Welcome back, Admin!</p>
                    </div>
                </div>
                
                <div class="top-nav-right">
                    <div class="admin-profile">
                        <img src="https://ui-avatars.com/api/?name=Admin+User&background=F28C28&color=fff&size=100" alt="Admin">
                    </div>
                </div>
            </nav>
            
            <!-- Content Area -->
            <div class="content-area">
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php switch($section): 
                    case 'dashboard': ?>
                        <!-- Dashboard Stats -->
                        <div class="stats-container">
                            <div class="stat-card stat-1">
                                <div class="stat-icon">
                                    <i class="fas fa-suitcase-rolling"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $packageCount ?></h3>
                                    <p>Total Packages</p>
                                </div>
                            </div>
                            
                            <div class="stat-card stat-2">
                                <div class="stat-icon">
                                    <i class="fas fa-hotel"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $hotelCount ?></h3>
                                    <p>Total Hotels</p>
                                </div>
                            </div>
                            
                            <div class="stat-card stat-3">
                                <div class="stat-icon">
                                    <i class="fas fa-images"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $carouselCount ?></h3>
                                    <p>Carousel Images</p>
                                </div>
                            </div>
                            
                            <div class="stat-card stat-4">
                                <div class="stat-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $activeCarouselCount ?></h3>
                                    <p>Active Images</p>
                                </div>
                            </div>
                            
                            <div class="stat-card stat-5">
                                <div class="stat-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $destinationCount ?></h3>
                                    <p>Popular Destinations</p>
                                </div>
                            </div>
                            
                            <div class="stat-card stat-6">
                                <div class="stat-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $total_enquiries ?></h3>
                                    <p>Tour Enquiries</p>
                                </div>
                            </div>
                            
                            <div class="stat-card stat-7">
                                <div class="stat-icon" style="background-color: #e67e22;">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $total_hotel_enquiries ?></h3>
                                    <p>Hotel Enquiries</p>
                                </div>
                            </div>
                            <div class="stat-card stat-8">
                                <div class="stat-icon" style="background-color: #9b59b6;">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?= $total_contacts ?></h3>
                                    <p>Contact Messages</p>
                                </div>
                            </div>
                            <div class="stat-card" style="border-left-color: #f1c40f;">
                                    <div class="stat-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3><?= $total_feedbacks ?></h3>
                                        <p>Total Feedbacks</p>
                                        <small>Avg Rating: <?= $average_rating ?> ⭐</small>
                                    </div>
                                </div>
                        </div>
                        
                        <!-- Recent Enquiries -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-clock"></i> Recent Tour Enquiries</h5>
                                        <a href="?section=enquiries" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            <?php 
                                            $recent_enquiries = array_slice($enquiries, 0, 5);
                                            foreach($recent_enquiries as $e): 
                                            ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= htmlspecialchars($e['full_name']) ?></strong>
                                                        <br>
                                                        <small><?= htmlspecialchars($e['package_name'] ?: $e['package_title'] ?: 'General Enquiry') ?></small>
                                                    </div>
                                                    <span class="badge <?= $e['status'] == 'New' ? 'bg-danger' : ($e['status'] == 'In Progress' ? 'bg-warning' : ($e['status'] == 'Closed' ? 'bg-success' : 'bg-secondary')) ?>">
                                                        <?= $e['status'] ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted"><?= date('d M Y', strtotime($e['created_at'])) ?></small>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-concierge-bell"></i> Recent Service Bookings</h5>
                                        <a href="?section=other_services" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            <?php 
                                            $recent_services = array_slice($other_services, 0, 5);
                                            foreach($recent_services as $s): 
                                            ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                                                        <br>
                                                        <small><?= htmlspecialchars($s['package_name']) ?></small>
                                                    </div>
                                                    <span class="badge <?= $s['status'] == 'New' ? 'bg-danger' : ($s['status'] == 'In Progress' ? 'bg-warning' : ($s['status'] == 'Closed' ? 'bg-success' : 'bg-secondary')) ?>">
                                                        <?= $s['status'] ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted"><?= date('d M Y', strtotime($s['created_at'])) ?></small>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-bed"></i> Recent Hotel Enquiries</h5>
                                        <a href="?section=hotel_enquiries" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            <?php 
                                            $recent_hotel_enquiries = array_slice($hotel_enquiries, 0, 5);
                                            foreach($recent_hotel_enquiries as $he): 
                                            ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= htmlspecialchars($he['full_name']) ?></strong>
                                                        <br>
                                                        <small><?= htmlspecialchars($he['hotel_name'] ?? 'N/A') ?></small>
                                                    </div>
                                                    <span class="badge <?= $he['status'] == 'New' ? 'bg-danger' : ($he['status'] == 'In Progress' ? 'bg-warning' : ($he['status'] == 'Closed' ? 'bg-success' : 'bg-secondary')) ?>">
                                                        <?= $he['status'] ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted"><?= date('d M Y', strtotime($he['created_at'])) ?></small>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    
                    case 'packages': ?>
                        <!-- Tour Packages Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-suitcase-rolling"></i> Manage Tour Packages</h5>
                                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                                    <i class="fas fa-plus"></i> Add New Package
                                </button>
                            </div>
                        
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="packagesTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Destination</th>
                                                <th>Type</th>
                                                <th>Price</th>
                                                <th>Duration</th>
                                                <th>People</th>
                                                <th>Features</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                        
                                        <tbody>
                                        <?php foreach ($packages as $p): ?>
                                            <tr>
                                                <td><?= $p['id'] ?></td>
                                                <td>
                                                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                                                </td>
                                                <td><?= htmlspecialchars($p['title']) ?></td>
                                                <td>
                                                    <?php if($p['destination_name']): ?>
                                                        <a href="?section=destinations" class="badge bg-info text-decoration-none">
                                                            <?= htmlspecialchars($p['destination_name']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Not Set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $p['package_type'] === 'International' ? 'bg-danger' : 'bg-primary' ?>">
                                                        <?= $p['package_type'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($p['price_type'] == 'starting_from'): ?>
                                                        <span class="badge price-badge">From ₹<?= number_format($p['price'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge price-badge">₹<?= number_format($p['price'], 2) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge nights-badge">
                                                        <?= $p['days'] ?? '?' ?>D / <?= $p['nights'] ?? '?' ?>N
                                                    </span>
                                                    <small class="d-block text-muted"><?= htmlspecialchars($p['duration']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">Min: <?= $p['min_people'] ?? 1 ?></span>
                                                </td>
                                                <td style="max-width:200px;">
                                                    <?php
                                                    $features = array_filter(array_map('trim', explode(',', $p['features'] ?? '')));
                                                    foreach (array_slice($features, 0, 2) as $feature):
                                                    ?>
                                                        <span class="feature-badge">
                                                            <?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                    <?php if(count($features) > 2): ?>
                                                        <span class="feature-badge">+<?= count($features) - 2 ?> more</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $p['status'] === 'Active' ? 'bg-success' : 
                                                           ($p['status'] === 'Draft' ? 'bg-warning' : 'bg-secondary') ?>">
                                                        <?= $p['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm btn-action"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPackageModal"
                                                        data-id="<?= $p['id'] ?>"
                                                        data-title="<?= htmlspecialchars($p['title']) ?>"
                                                        data-destination="<?= $p['destination_id'] ?>"
                                                        data-type="<?= $p['package_type'] ?>"
                                                        data-price="<?= $p['price'] ?>"
                                                        data-price_type="<?= $p['price_type'] ?? 'fixed' ?>"
                                                        data-min_people="<?= $p['min_people'] ?? 1 ?>"
                                                        data-duration="<?= $p['duration'] ?>"
                                                        data-days="<?= $p['days'] ?? '' ?>"
                                                        data-nights="<?= $p['nights'] ?? '' ?>"
                                                        data-status="<?= $p['status'] ?>"
                                                        data-description="<?= htmlspecialchars($p['description']) ?>"
                                                        data-features='<?= json_encode($features, JSON_UNESCAPED_UNICODE) ?>'
                                                        data-locations='<?= json_encode(array_filter(array_map('trim', explode(',', $p['locations_covered'] ?? '')))) ?>'
                                                        data-inclusions='<?= json_encode(array_filter(array_map('trim', explode(',', $p['inclusions'] ?? '')))) ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?section=packages&delete=<?= $p['id'] ?>"
                                                       onclick="return confirm('Delete this package?')"
                                                       class="btn btn-danger btn-sm btn-action">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    
                    case 'hotels': ?>
                        <!-- Hotels Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-hotel"></i> Manage Hotels</h5>
                                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                                    <i class="fas fa-plus"></i> Add New Hotel
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="hotelsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Hotel Name</th>
                                                <th>Destination</th>
                                                <th>Category</th>
                                                <th>Price/Night</th>
                                                <th>Features</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($hotels as $h): 
                                                $features = array_filter(array_map('trim', explode(',', $h['features'])));
                                            ?>
                                            <tr>
                                                <td><?= $h['id'] ?></td>
                                                <td>
                                                    <img src="../uploads/<?= htmlspecialchars($h['image']) ?>" alt="<?= htmlspecialchars($h['hotel_name']) ?>">
                                                </td>
                                                <td><?= htmlspecialchars($h['hotel_name']) ?></td>
                                                <td>
                                                    <?php if($h['destination_name']): ?>
                                                        <a href="?section=destinations" class="badge bg-info text-decoration-none">
                                                            <?= htmlspecialchars($h['destination_name']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Not Set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $h['category'] ?></span>
                                                </td>
                                                <td>₹<?= number_format($h['price_per_night'], 2) ?></td>
                                                <td style="max-width: 200px;">
                                                    <?php foreach(array_slice($features, 0, 3) as $feature): ?>
                                                        <span class="feature-badge"><?= htmlspecialchars($feature) ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if(count($features) > 3): ?>
                                                        <span class="feature-badge">+<?= count($features) - 3 ?> more</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $h['status'] == 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= $h['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm btn-action"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editHotelModal"
                                                            data-id="<?= $h['id'] ?>"
                                                            data-name="<?= htmlspecialchars($h['hotel_name']) ?>"
                                                            data-destination="<?= $h['destination_id'] ?>"
                                                            data-category="<?= $h['category'] ?>"
                                                            data-price="<?= $h['price_per_night'] ?>"
                                                            data-status="<?= $h['status'] ?>"
                                                            data-description="<?= htmlspecialchars($h['description']) ?>"
                                                            data-features='<?= json_encode($features) ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?section=hotels&delete_hotel=<?= $h['id'] ?>" 
                                                       onclick="return confirm('Delete this hotel?')"
                                                       class="btn btn-danger btn-sm btn-action">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    
                    case 'hero': ?>
                        <!-- Hero Section Management -->
                        <div class="row">
                            <!-- Hero Content Management -->
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-edit"></i> Hero Content</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Main Title</label>
                                                <input type="text" name="main_title" class="form-control" 
                                                       value="<?= htmlspecialchars($hero_content['main_title']) ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="main_description" class="form-control" rows="4" required><?= htmlspecialchars($hero_content['main_description']) ?></textarea>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Button Text</label>
                                                    <input type="text" name="button_text" class="form-control" 
                                                           value="<?= htmlspecialchars($hero_content['button_text']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Button Link</label>
                                                    <input type="text" name="button_link" class="form-control" 
                                                           value="<?= htmlspecialchars($hero_content['button_link']) ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="is_active" class="form-control">
                                                    <option value="1" <?= $hero_content['is_active'] ? 'selected' : '' ?>>Active</option>
                                                    <option value="0" <?= !$hero_content['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                            
                                            <button type="submit" name="update_hero_content" class="btn-submit">
                                                <i class="fas fa-save"></i> Update Content
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Carousel Images Management -->
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-sliders-h"></i> Background Carousel</h5>
                                        <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addCarouselModal">
                                            <i class="fas fa-plus"></i> Add Image
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Image</th>
                                                        <th>Title</th>
                                                        <th>Order</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($carousel_images as $index => $image): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td>
                                                            <img src="../<?= htmlspecialchars($image['thumbnail_url']) ?>" 
                                                                 alt="<?= htmlspecialchars($image['alt_text']) ?>"
                                                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                        </td>
                                                        <td><?= htmlspecialchars($image['title']) ?></td>
                                                        <td><?= $image['display_order'] ?></td>
                                                        <td>
                                                            <span class="badge <?= $image['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                                <?= $image['is_active'] ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-warning btn-sm btn-action"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editCarouselModal"
                                                                    data-id="<?= $image['id'] ?>"
                                                                    data-title="<?= htmlspecialchars($image['title']) ?>"
                                                                    data-alt="<?= htmlspecialchars($image['alt_text']) ?>"
                                                                    data-description="<?= htmlspecialchars($image['description']) ?>"
                                                                    data-order="<?= $image['display_order'] ?>"
                                                                    data-status="<?= $image['is_active'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="?section=hero&delete_carousel=<?= $image['id'] ?>" 
                                                               onclick="return confirm('Delete this image?')"
                                                               class="btn btn-danger btn-sm btn-action">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview Section -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-eye"></i> Hero Section Preview</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="preview-container">
                                    <!-- Background Carousel -->
                                    <div class="background-carousel">
                                        <?php 
                                        $active_images = array_filter($carousel_images, function($img) {
                                            return $img['is_active'];
                                        });
                                        $active_images = array_values($active_images);
                                        ?>
                                        <?php if(count($active_images) > 0): ?>
                                            <?php foreach($active_images as $index => $image): ?>
                                                <div class="bg-image <?= $index === 0 ? 'active' : '' ?>" 
                                                     id="previewImage<?= $index ?>"
                                                     style="background-image: url('../<?= $image['image_url'] ?>');">
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="bg-image active" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Content Overlay -->
                                    <div class="hero-overlay">
                                        <div class="hero-content">
                                            <h1><?= htmlspecialchars($hero_content['main_title']) ?></h1>
                                            <p><?= htmlspecialchars($hero_content['main_description']) ?></p>
                                            <a href="<?= htmlspecialchars($hero_content['button_link']) ?>" 
                                               class="btn-hero">
                                                <?= htmlspecialchars($hero_content['button_text']) ?>
                                            </a>
                                            
                                            <!-- Thumbnails Preview -->
                                            <?php if(count($active_images) > 0): ?>
                                                <div class="thumbnail-strip">
                                                    <?php foreach($active_images as $index => $img): ?>
                                                        <img src="../<?= $img['thumbnail_url'] ?>" 
                                                             alt="<?= htmlspecialchars($img['alt_text']) ?>"
                                                             class="<?= $index === 0 ? 'active' : '' ?>"
                                                             onmouseover="switchPreviewImage(<?= $index ?>)"
                                                             id="previewThumb<?= $index ?>">
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    
                    case 'destinations': ?>
                        <!-- Popular Destinations Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-map-marker-alt"></i> Manage Popular Destinations</h5>
                                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addDestinationModal">
                                    <i class="fas fa-plus"></i> Add New Destination
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="destinationsTable">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <th>Display Order</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($destinations as $d): ?>
                                            <tr>
                                                <td><?= $d['id'] ?></td>
                                                <td>
                                                    <img src="../uploads/<?= htmlspecialchars($d['image']) ?>" 
                                                         alt="<?= htmlspecialchars($d['title']) ?>">
                                                </td>
                                                <td><?= htmlspecialchars($d['title']) ?></td>
                                                <td>
                                                    <code><?= htmlspecialchars($d['slug']) ?></code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= $d['display_order'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $d['status'] == 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= $d['status'] ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm btn-action"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editDestinationModal"
                                                            data-id="<?= $d['id'] ?>"
                                                            data-title="<?= htmlspecialchars($d['title']) ?>"
                                                            data-slug="<?= htmlspecialchars($d['slug']) ?>"
                                                            data-status="<?= $d['status'] ?>"
                                                            data-order="<?= $d['display_order'] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?section=destinations&delete_destination=<?= $d['id'] ?>" 
                                                       onclick="return confirm('Delete this destination?')"
                                                       class="btn btn-danger btn-sm btn-action">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    case 'feedbacks': ?>
                        <!-- Feedbacks Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-star"></i> Manage Feedbacks & Reviews</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportFeedbacksModal">
                                        <i class="fas fa-download"></i> Export to CSV
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Filter Section -->
                            <div class="filter-section">
                                <form method="GET" class="row g-3 align-items-end">
                                    <input type="hidden" name="section" value="feedbacks">
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Search</label>
                                        <input type="text" name="feedback_search" class="form-control" 
                                               placeholder="Name, email, message..." value="<?= htmlspecialchars($feedback_search) ?>">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="feedback_filter_status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="New" <?= $feedback_filter_status == 'New' ? 'selected' : '' ?>>New</option>
                                            <option value="Published" <?= $feedback_filter_status == 'Published' ? 'selected' : '' ?>>Published</option>
                                            <option value="Pending" <?= $feedback_filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Rejected" <?= $feedback_filter_status == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                            <option value="Archived" <?= $feedback_filter_status == 'Archived' ? 'selected' : '' ?>>Archived</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Rating</label>
                                        <select name="feedback_filter_rating" class="form-select">
                                            <option value="">All Ratings</option>
                                            <option value="5" <?= $feedback_filter_rating == '5' ? 'selected' : '' ?>>5 Stars</option>
                                            <option value="4" <?= $feedback_filter_rating == '4' ? 'selected' : '' ?>>4 Stars</option>
                                            <option value="3" <?= $feedback_filter_rating == '3' ? 'selected' : '' ?>>3 Stars</option>
                                            <option value="2" <?= $feedback_filter_rating == '2' ? 'selected' : '' ?>>2 Stars</option>
                                            <option value="1" <?= $feedback_filter_rating == '1' ? 'selected' : '' ?>>1 Star</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">From Date</label>
                                        <input type="date" name="feedback_date_from" class="form-control" value="<?= $feedback_date_from ?>">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">To Date</label>
                                        <input type="date" name="feedback_date_to" class="form-control" value="<?= $feedback_date_to ?>">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Apply
                                            </button>
                                            <a href="?section=feedbacks" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="row px-3 pb-3">
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-light p-2 rounded text-center">
                                        <span class="fw-bold">Total: <?= $total_feedbacks ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-danger text-white p-2 rounded text-center">
                                        <span class="fw-bold">New: <?= $new_feedbacks ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-success text-white p-2 rounded text-center">
                                        <span class="fw-bold">Published: <?= $published_feedbacks ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-warning p-2 rounded text-center">
                                        <span class="fw-bold">Pending: <?= $pending_feedbacks ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-secondary text-white p-2 rounded text-center">
                                        <span class="fw-bold">Avg Rating: <?= $average_rating ?> ⭐</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rating Distribution -->
                            <div class="row px-3 pb-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="fw-bold">Rating Distribution:</span>
                                        <?php for($i = 5; $i >= 1; $i--): ?>
                                            <div class="d-flex align-items-center">
                                                <span class="me-1"><?= $i ?>⭐</span>
                                                <div class="progress" style="width: 100px; height: 20px;">
                                                    <?php 
                                                    $percentage = $total_feedbacks > 0 ? round(($rating_counts[$i] / $total_feedbacks) * 100) : 0;
                                                    ?>
                                                    <div class="progress-bar bg-warning" style="width: <?= $percentage ?>%">
                                                        <?= $rating_counts[$i] ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="px-3 pb-0">
                                <form method="POST" id="bulkFeedbackActionForm">
                                    <div class="bulk-actions">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllFeedback">
                                            <label class="form-check-label" for="selectAllFeedback">Select All</label>
                                        </div>
                                        <select name="bulk_feedback_action" class="form-select form-select-sm w-auto">
                                            <option value="">Bulk Actions</option>
                                            <option value="publish">Publish</option>
                                            <option value="pending">Mark as Pending</option>
                                            <option value="reject">Reject</option>
                                            <option value="archive">Archive</option>
                                            <option value="delete_selected">Delete Selected</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkFeedbackAction()">Apply</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="feedbacksTable">
                                        <thead>
                                            <tr>
                                                <th width="30">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                    </div>
                                                </th>
                                                <th>ID</th>
                                                <th>Rating</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Subject</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Admin Notes</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($feedbacks)): ?>
                                                <tr>
                                                    <td colspan="11" class="text-center py-4">
                                                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No feedbacks found</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($feedbacks as $fb): ?>
                                                <tr class="<?= $fb['status'] == 'New' ? 'table-warning' : '' ?>">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input feedback-checkbox" type="checkbox" name="selected_feedback_ids[]" value="<?= $fb['id'] ?>" form="bulkFeedbackActionForm">
                                                        </div>
                                                    </td>
                                                    <td><?= $fb['id'] ?></td>
                                                    <td>
                                                        <?php if($fb['rating']): ?>
                                                            <span class="text-warning">
                                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                                    <?php if($i <= $fb['rating']): ?>
                                                                        <i class="fas fa-star"></i>
                                                                    <?php else: ?>
                                                                        <i class="far fa-star"></i>
                                                                    <?php endif; ?>
                                                                <?php endfor; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">No rating</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($fb['name']) ?></strong>
                                                        <?php if($fb['status'] == 'New'): ?>
                                                            <span class="badge bg-danger ms-1">New</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div><i class="fas fa-envelope fa-xs me-1"></i> <?= htmlspecialchars($fb['email']) ?></div>
                                                        <?php if(!empty($fb['phone'])): ?>
                                                            <div><i class="fas fa-phone fa-xs me-1"></i> <?= htmlspecialchars($fb['phone']) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($fb['subject'])): ?>
                                                            <?= htmlspecialchars($fb['subject']) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="max-width: 200px;">
                                                        <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                            <?= htmlspecialchars($fb['message']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="?section=feedbacks" style="display: inline;">
                                                            <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                                                            <input type="hidden" name="update_feedback_status" value="1">
                                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; min-width: 90px;">
                                                                <option value="New" <?= $fb['status'] == 'New' ? 'selected' : '' ?>>New</option>
                                                                <option value="Published" <?= $fb['status'] == 'Published' ? 'selected' : '' ?>>Published</option>
                                                                <option value="Pending" <?= $fb['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                                <option value="Rejected" <?= $fb['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                                                <option value="Archived" <?= $fb['status'] == 'Archived' ? 'selected' : '' ?>>Archived</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editFeedbackNotesModal"
                                                                data-id="<?= $fb['id'] ?>"
                                                                data-notes="<?= htmlspecialchars($fb['admin_notes'] ?? '') ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <div><?= date('d M Y', strtotime($fb['created_at'])) ?></div>
                                                        <small class="text-muted"><?= date('H:i', strtotime($fb['created_at'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm btn-action"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewFeedbackModal"
                                                                data-id="<?= $fb['id'] ?>"
                                                                data-name="<?= htmlspecialchars($fb['name']) ?>"
                                                                data-email="<?= htmlspecialchars($fb['email']) ?>"
                                                                data-phone="<?= htmlspecialchars($fb['phone'] ?? 'Not provided') ?>"
                                                                data-subject="<?= htmlspecialchars($fb['subject'] ?? 'No subject') ?>"
                                                                data-message="<?= htmlspecialchars($fb['message']) ?>"
                                                                data-rating="<?= $fb['rating'] ?? 'Not rated' ?>"
                                                                data-status="<?= $fb['status'] ?>"
                                                                data-notes="<?= htmlspecialchars($fb['admin_notes'] ?? 'No admin notes') ?>"
                                                                data-ip="<?= htmlspecialchars($fb['ip_address'] ?? 'N/A') ?>"
                                                                data-page="<?= htmlspecialchars($fb['page_url'] ?? 'N/A') ?>"
                                                                data-useragent="<?= htmlspecialchars($fb['user_agent'] ?? 'N/A') ?>"
                                                                data-created="<?= date('d M Y H:i', strtotime($fb['created_at'])) ?>"
                                                                data-updated="<?= !empty($fb['updated_at']) ? date('d M Y H:i', strtotime($fb['updated_at'])) : 'Not updated' ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="?section=feedbacks&delete_feedback=<?= $fb['id'] ?>" 
                                                           onclick="return confirm('Delete this feedback?')"
                                                           class="btn btn-danger btn-sm btn-action">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    case 'enquiries': ?>
                        <!-- Enquiries Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-envelope"></i> Manage Enquiries</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportEnquiries()">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Filter Section -->
                            <div class="filter-section">
                                <form method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="section" value="enquiries">
                                    <div class="col-auto">
                                        <label class="visually-hidden">Filter by Status</label>
                                        <select name="enquiry_status" class="form-select form-select-sm">
                                            <option value="">All Statuses</option>
                                            <option value="New" <?= $enquiry_status == 'New' ? 'selected' : '' ?>>New</option>
                                            <option value="Read" <?= $enquiry_status == 'Read' ? 'selected' : '' ?>>Read</option>
                                            <option value="In Progress" <?= $enquiry_status == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Closed" <?= $enquiry_status == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label class="visually-hidden">Filter by Source</label>
                                        <select name="enquiry_source" class="form-select form-select-sm">
                                            <option value="">All Sources</option>
                                            <?php foreach($sources as $source): ?>
                                                <option value="<?= $source ?>" <?= $enquiry_source == $source ? 'selected' : '' ?>>
                                                    <?= ucfirst($source) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-filter"></i> Apply Filters
                                        </button>
                                        <a href="?section=enquiries" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="px-3 pb-0">
                                <form method="POST" id="bulkActionForm">
                                    <div class="bulk-actions">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll">Select All</label>
                                        </div>
                                        <select name="bulk_action" class="form-select form-select-sm w-auto">
                                            <option value="">Bulk Actions</option>
                                            <option value="mark_read">Mark as Read</option>
                                            <option value="mark_in_progress">Mark as In Progress</option>
                                            <option value="delete_selected">Delete Selected</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkAction()">Apply</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="enquiriesTable">
                                        <thead>
                                            <tr>
                                                <th width="30">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                    </div>
                                                </th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Package</th>
                                                <th>Travel Date</th>
                                                <th>Travelers</th>
                                                <th>Source</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($enquiries as $e): ?>
                                            <tr class="<?= $e['status'] == 'New' ? 'table-warning' : '' ?>">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input enquiry-checkbox" type="checkbox" name="selected_ids[]" value="<?= $e['id'] ?>" form="bulkActionForm">
                                                    </div>
                                                </td>
                                                <td><?= $e['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($e['full_name']) ?></strong>
                                                    <?php if($e['status'] == 'New'): ?>
                                                        <span class="badge bg-danger ms-1">New</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div><i class="fas fa-envelope fa-xs me-1"></i> <?= htmlspecialchars($e['email']) ?></div>
                                                    <div><i class="fas fa-phone fa-xs me-1"></i> <?= htmlspecialchars($e['phone']) ?></div>
                                                </td>
                                                <td>
                                                    <?php if($e['package_name']): ?>
                                                        <?= htmlspecialchars($e['package_name']) ?>
                                                        <?php if($e['package_title']): ?>
                                                            <br><small class="text-muted">(<?= htmlspecialchars($e['package_title']) ?>)</small>
                                                        <?php endif; ?>
                                                    <?php elseif($e['package_title']): ?>
                                                        <?= htmlspecialchars($e['package_title']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">General Enquiry</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $e['travel_date'] ? date('d M Y', strtotime($e['travel_date'])) : '-' ?></td>
                                                <td><?= htmlspecialchars($e['travelers'] ?: '-') ?></td>
                                                <td>
                                                    <span class="source-badge"><?= ucfirst($e['source'] ?: 'unknown') ?></span>
                                                </td>
                                                <td>
                                                    <form method="POST" action="?section=enquiries" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                        <input type="hidden" name="update_enquiry_status" value="1">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                                            <option value="New" <?= $e['status'] == 'New' ? 'selected' : '' ?>>New</option>
                                                            <option value="Read" <?= $e['status'] == 'Read' ? 'selected' : '' ?>>Read</option>
                                                            <option value="In Progress" <?= $e['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                            <option value="Closed" <?= $e['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td><?= date('d M Y H:i', strtotime($e['created_at'])) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-sm btn-action"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewEnquiryModal"
                                                            data-id="<?= $e['id'] ?>"
                                                            data-name="<?= htmlspecialchars($e['full_name']) ?>"
                                                            data-email="<?= htmlspecialchars($e['email']) ?>"
                                                            data-phone="<?= htmlspecialchars($e['phone']) ?>"
                                                            data-package="<?= htmlspecialchars($e['package_name'] ?: $e['package_title'] ?: 'General Enquiry') ?>"
                                                            data-traveldate="<?= $e['travel_date'] ? date('d M Y', strtotime($e['travel_date'])) : '-' ?>"
                                                            data-travelers="<?= htmlspecialchars($e['travelers'] ?: '-') ?>"
                                                            data-message="<?= htmlspecialchars($e['message']) ?>"
                                                            data-source="<?= ucfirst($e['source'] ?: 'unknown') ?>"
                                                            data-status="<?= $e['status'] ?>"
                                                            data-created="<?= date('d M Y H:i', strtotime($e['created_at'])) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="?section=enquiries&delete_enquiry=<?= $e['id'] ?>" 
                                                       onclick="return confirm('Delete this enquiry?')"
                                                       class="btn btn-danger btn-sm btn-action">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    
                    case 'other_services': ?>
                        <!-- Other Services Enquiries Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-concierge-bell"></i> Manage Service Bookings</h5>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="otherServicesTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Service/Package</th>
                                                <th>Travel Date</th>
                                                <th>Travelers</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($other_services as $s): ?>
                                            <tr class="<?= $s['status'] == 'New' ? 'table-warning' : '' ?>">
                                                <td><?= $s['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                                                    <?php if($s['status'] == 'New'): ?>
                                                        <span class="badge bg-danger ms-1">New</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div><i class="fas fa-envelope fa-xs me-1"></i> <?= htmlspecialchars($s['email']) ?></div>
                                                    <div><i class="fas fa-phone fa-xs me-1"></i> <?= htmlspecialchars($s['phone']) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($s['package_name']) ?></td>
                                                <td><?= date('d M Y', strtotime($s['travel_date'])) ?></td>
                                                <td><?= htmlspecialchars($s['travelers']) ?></td>
                                                <td>
                                                    <form method="POST" action="?section=other_services" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                        <input type="hidden" name="update_other_service_status" value="1">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                                            <option value="New" <?= $s['status'] == 'New' ? 'selected' : '' ?>>New</option>
                                                            <option value="Read" <?= $s['status'] == 'Read' ? 'selected' : '' ?>>Read</option>
                                                            <option value="In Progress" <?= $s['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                            <option value="Closed" <?= $s['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td><?= date('d M Y H:i', strtotime($s['created_at'])) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-sm btn-action"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewOtherServiceModal"
                                                            data-id="<?= $s['id'] ?>"
                                                            data-name="<?= htmlspecialchars($s['full_name']) ?>"
                                                            data-email="<?= htmlspecialchars($s['email']) ?>"
                                                            data-phone="<?= htmlspecialchars($s['phone']) ?>"
                                                            data-package="<?= htmlspecialchars($s['package_name']) ?>"
                                                            data-traveldate="<?= date('d M Y', strtotime($s['travel_date'])) ?>"
                                                            data-travelers="<?= htmlspecialchars($s['travelers']) ?>"
                                                            data-message="<?= htmlspecialchars($s['message']) ?>"
                                                            data-status="<?= $s['status'] ?>"
                                                            data-created="<?= date('d M Y H:i', strtotime($s['created_at'])) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="?section=other_services&delete_other_service=<?= $s['id'] ?>" 
                                                       onclick="return confirm('Delete this service enquiry?')"
                                                       class="btn btn-danger btn-sm btn-action">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    
                    case 'hotel_enquiries': ?>
                        <!-- Hotel Enquiries Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bed"></i> Manage Hotel Enquiries</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportHotelEnquiriesModal">
                                        <i class="fas fa-download"></i> Export to Excel
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Filter Section -->
                            <div class="filter-section">
                                <form method="GET" class="row g-3 align-items-end">
                                    <input type="hidden" name="section" value="hotel_enquiries">
                                    
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Filter by Hotel</label>
                                        <select name="hotel_filter" class="form-select">
                                            <option value="">All Hotels</option>
                                            <?php foreach($all_hotels as $hotel): ?>
                                                <option value="<?= $hotel['id'] ?>" <?= $hotel_filter == $hotel['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($hotel['hotel_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="hotel_status_filter" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="New" <?= $hotel_status_filter == 'New' ? 'selected' : '' ?>>New</option>
                                            <option value="Read" <?= $hotel_status_filter == 'Read' ? 'selected' : '' ?>>Read</option>
                                            <option value="In Progress" <?= $hotel_status_filter == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Closed" <?= $hotel_status_filter == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                            <option value="Cancelled" <?= $hotel_status_filter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">From Date</label>
                                        <input type="date" name="hotel_date_from" class="form-control" value="<?= $hotel_date_from ?>">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">To Date</label>
                                        <input type="date" name="hotel_date_to" class="form-control" value="<?= $hotel_date_to ?>">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Apply Filters
                                            </button>
                                            <a href="?section=hotel_enquiries" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="row px-3 pb-3">
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-light p-2 rounded text-center">
                                        <span class="fw-bold">Total: <?= $total_hotel_enquiries ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-danger text-white p-2 rounded text-center">
                                        <span class="fw-bold">New: <?= $new_hotel_enquiries ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-secondary text-white p-2 rounded text-center">
                                        <span class="fw-bold">Read: <?= $read_hotel_enquiries ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-warning p-2 rounded text-center">
                                        <span class="fw-bold">In Progress: <?= $in_progress_hotel_enquiries ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-success text-white p-2 rounded text-center">
                                        <span class="fw-bold">Closed: <?= $closed_hotel_enquiries ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-dark text-white p-2 rounded text-center">
                                        <span class="fw-bold">Cancelled: <?= $cancelled_hotel_enquiries ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="px-3 pb-0">
                                <form method="POST" id="bulkHotelActionForm">
                                    <div class="bulk-actions">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllHotel">
                                            <label class="form-check-label" for="selectAllHotel">Select All</label>
                                        </div>
                                        <select name="bulk_hotel_action" class="form-select form-select-sm w-auto">
                                            <option value="">Bulk Actions</option>
                                            <option value="mark_read">Mark as Read</option>
                                            <option value="mark_in_progress">Mark as In Progress</option>
                                            <option value="mark_closed">Mark as Closed</option>
                                            <option value="delete_selected">Delete Selected</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkHotelAction()">Apply</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="hotelEnquiriesTable">
                                        <thead>
                                            <tr>
                                                <th width="30">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                    </div>
                                                </th>
                                                <th>ID</th>
                                                <th>Hotel</th>
                                                <th>Guest</th>
                                                <th>Contact</th>
                                                <th>Check-In/Out</th>
                                                <th>Guests/Rooms</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($hotel_enquiries)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-4">
                                                        <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No hotel enquiries found</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($hotel_enquiries as $he): ?>
                                                <tr class="<?= $he['status'] == 'New' ? 'table-warning' : '' ?>">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input hotel-enquiry-checkbox" type="checkbox" name="selected_hotel_ids[]" value="<?= $he['id'] ?>" form="bulkHotelActionForm">
                                                        </div>
                                                    </td>
                                                    <td><?= $he['id'] ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($he['hotel_name'] ?? 'N/A') ?></strong>
                                                        <?php if(!empty($he['destination_name'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($he['destination_name']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($he['full_name']) ?></strong>
                                                        <?php if($he['status'] == 'New'): ?>
                                                            <span class="badge bg-danger ms-1">New</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div><i class="fas fa-envelope fa-xs me-1"></i> <?= htmlspecialchars($he['email']) ?></div>
                                                        <div><i class="fas fa-phone fa-xs me-1"></i> <?= htmlspecialchars($he['phone']) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($he['check_in_date'])): ?>
                                                            <div><i class="fas fa-calendar-check me-1"></i> <?= date('d M Y', strtotime($he['check_in_date'])) ?></div>
                                                            <div><i class="fas fa-calendar-times me-1"></i> <?= date('d M Y', strtotime($he['check_out_date'])) ?></div>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not specified</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($he['guests'])): ?>
                                                            <span class="badge bg-info"><i class="fas fa-user me-1"></i> <?= htmlspecialchars($he['guests']) ?></span>
                                                        <?php endif; ?>
                                                        <?php if(!empty($he['rooms'])): ?>
                                                            <span class="badge bg-secondary"><i class="fas fa-door-open me-1"></i> <?= $he['rooms'] ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="?section=hotel_enquiries" style="display: inline;">
                                                            <input type="hidden" name="id" value="<?= $he['id'] ?>">
                                                            <input type="hidden" name="update_hotel_enquiry_status" value="1">
                                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; min-width: 100px;">
                                                                <option value="New" <?= $he['status'] == 'New' ? 'selected' : '' ?>>New</option>
                                                                <option value="Read" <?= $he['status'] == 'Read' ? 'selected' : '' ?>>Read</option>
                                                                <option value="In Progress" <?= $he['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                                <option value="Closed" <?= $he['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                                                <option value="Cancelled" <?= $he['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <div><?= date('d M Y', strtotime($he['created_at'])) ?></div>
                                                        <small class="text-muted"><?= date('H:i', strtotime($he['created_at'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm btn-action"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewHotelEnquiryModal"
                                                                data-id="<?= $he['id'] ?>"
                                                                data-hotel="<?= htmlspecialchars($he['hotel_name'] ?? 'N/A') ?>"
                                                                data-destination="<?= htmlspecialchars($he['destination_name'] ?? 'N/A') ?>"
                                                                data-name="<?= htmlspecialchars($he['full_name']) ?>"
                                                                data-email="<?= htmlspecialchars($he['email']) ?>"
                                                                data-phone="<?= htmlspecialchars($he['phone']) ?>"
                                                                data-checkin="<?= !empty($he['check_in_date']) ? date('d M Y', strtotime($he['check_in_date'])) : 'Not specified' ?>"
                                                                data-checkout="<?= !empty($he['check_out_date']) ? date('d M Y', strtotime($he['check_out_date'])) : 'Not specified' ?>"
                                                                data-guests="<?= htmlspecialchars($he['guests'] ?? 'Not specified') ?>"
                                                                data-rooms="<?= htmlspecialchars($he['rooms'] ?? 'Not specified') ?>"
                                                                data-message="<?= htmlspecialchars($he['message'] ?? 'No message') ?>"
                                                                data-status="<?= $he['status'] ?>"
                                                                data-source="<?= ucfirst($he['source'] ?? 'website') ?>"
                                                                data-created="<?= date('d M Y H:i', strtotime($he['created_at'])) ?>"
                                                                data-updated="<?= !empty($he['updated_at']) ? date('d M Y H:i', strtotime($he['updated_at'])) : 'Not updated' ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="?section=hotel_enquiries&delete_hotel_enquiry=<?= $he['id'] ?>" 
                                                           onclick="return confirm('Delete this hotel enquiry?')"
                                                           class="btn btn-danger btn-sm btn-action">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                    case 'contact_messages': ?>
                        <!-- Contact Messages Management -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-envelope-open-text"></i> Manage Contact Messages</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportContactMessagesModal">
                                        <i class="fas fa-download"></i> Export to CSV
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Filter Section -->
                            <div class="filter-section">
                                <form method="GET" class="row g-3 align-items-end">
                                    <input type="hidden" name="section" value="contact_messages">
                                    
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Filter by Status</label>
                                        <select name="contact_filter_status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="New" <?= $contact_filter_status == 'New' ? 'selected' : '' ?>>New</option>
                                            <option value="Read" <?= $contact_filter_status == 'Read' ? 'selected' : '' ?>>Read</option>
                                            <option value="Replied" <?= $contact_filter_status == 'Replied' ? 'selected' : '' ?>>Replied</option>
                                            <option value="Archived" <?= $contact_filter_status == 'Archived' ? 'selected' : '' ?>>Archived</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">From Date</label>
                                        <input type="date" name="contact_date_from" class="form-control" value="<?= $contact_date_from ?>">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">To Date</label>
                                        <input type="date" name="contact_date_to" class="form-control" value="<?= $contact_date_to ?>">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Apply Filters
                                            </button>
                                            <a href="?section=contact_messages" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="row px-3 pb-3">
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-light p-2 rounded text-center">
                                        <span class="fw-bold">Total: <?= $total_contacts ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-danger text-white p-2 rounded text-center">
                                        <span class="fw-bold">New: <?= $new_contacts ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-secondary text-white p-2 rounded text-center">
                                        <span class="fw-bold">Read: <?= $read_contacts ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-info text-white p-2 rounded text-center">
                                        <span class="fw-bold">Replied: <?= $replied_contacts ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
                                    <div class="small-card bg-dark text-white p-2 rounded text-center">
                                        <span class="fw-bold">Archived: <?= $archived_contacts ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="px-3 pb-0">
                                <form method="POST" id="bulkContactActionForm">
                                    <div class="bulk-actions">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllContact">
                                            <label class="form-check-label" for="selectAllContact">Select All</label>
                                        </div>
                                        <select name="bulk_contact_action" class="form-select form-select-sm w-auto">
                                            <option value="">Bulk Actions</option>
                                            <option value="mark_read">Mark as Read</option>
                                            <option value="mark_replied">Mark as Replied</option>
                                            <option value="mark_archived">Archive</option>
                                            <option value="delete_selected">Delete Selected</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkContactAction()">Apply</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="contactMessagesTable">
                                        <thead>
                                            <tr>
                                                <th width="30">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                    </div>
                                                </th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Subject</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>IP Address</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($contact_messages)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-4">
                                                        <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No contact messages found</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($contact_messages as $msg): ?>
                                                <tr class="<?= $msg['status'] == 'New' ? 'table-warning' : '' ?>">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input contact-checkbox" type="checkbox" name="selected_contact_ids[]" value="<?= $msg['id'] ?>" form="bulkContactActionForm">
                                                        </div>
                                                    </td>
                                                    <td><?= $msg['id'] ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($msg['full_name']) ?></strong>
                                                        <?php if($msg['status'] == 'New'): ?>
                                                            <span class="badge bg-danger ms-1">New</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div><i class="fas fa-envelope fa-xs me-1"></i> <?= htmlspecialchars($msg['email']) ?></div>
                                                        <?php if(!empty($msg['phone'])): ?>
                                                            <div><i class="fas fa-phone fa-xs me-1"></i> <?= htmlspecialchars($msg['phone']) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($msg['subject'])): ?>
                                                            <?= htmlspecialchars($msg['subject']) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">No subject</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="max-width: 200px;">
                                                        <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                            <?= htmlspecialchars($msg['message']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="?section=contact_messages" style="display: inline;">
                                                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                                            <input type="hidden" name="update_contact_status" value="1">
                                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; min-width: 90px;">
                                                                <option value="New" <?= $msg['status'] == 'New' ? 'selected' : '' ?>>New</option>
                                                                <option value="Read" <?= $msg['status'] == 'Read' ? 'selected' : '' ?>>Read</option>
                                                                <option value="Replied" <?= $msg['status'] == 'Replied' ? 'selected' : '' ?>>Replied</option>
                                                                <option value="Archived" <?= $msg['status'] == 'Archived' ? 'selected' : '' ?>>Archived</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($msg['ip_address'])): ?>
                                                            <small class="text-muted"><?= htmlspecialchars($msg['ip_address']) ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div><?= date('d M Y', strtotime($msg['created_at'])) ?></div>
                                                        <small class="text-muted"><?= date('H:i', strtotime($msg['created_at'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm btn-action"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewContactMessageModal"
                                                                data-id="<?= $msg['id'] ?>"
                                                                data-name="<?= htmlspecialchars($msg['full_name']) ?>"
                                                                data-email="<?= htmlspecialchars($msg['email']) ?>"
                                                                data-phone="<?= htmlspecialchars($msg['phone'] ?? 'Not provided') ?>"
                                                                data-subject="<?= htmlspecialchars($msg['subject'] ?? 'No subject') ?>"
                                                                data-message="<?= htmlspecialchars($msg['message']) ?>"
                                                                data-status="<?= $msg['status'] ?>"
                                                                data-ip="<?= htmlspecialchars($msg['ip_address'] ?? 'N/A') ?>"
                                                                data-useragent="<?= htmlspecialchars($msg['user_agent'] ?? 'N/A') ?>"
                                                                data-created="<?= date('d M Y H:i', strtotime($msg['created_at'])) ?>"
                                                                data-updated="<?= !empty($msg['updated_at']) ? date('d M Y H:i', strtotime($msg['updated_at'])) : 'Not updated' ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="?section=contact_messages&delete_contact=<?= $msg['id'] ?>" 
                                                           onclick="return confirm('Delete this contact message?')"
                                                           class="btn btn-danger btn-sm btn-action">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php break; 
                endswitch; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Tour Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <!-- Basic Info -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Package Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Destination *</label>
                                <select name="destination_id" class="form-control" required>
                                    <option value="">Select Destination</option>
                                    <?php foreach($active_destinations as $dest): ?>
                                        <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Package Type & Status -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Package Type *</label>
                                <select name="type" class="form-control" required>
                                    <option value="National">National</option>
                                    <option value="International">International</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Package Image *</label>
                                <input type="file" name="image" class="form-control" required>
                            </div>
                        </div>
                        
                        <!-- Duration Details -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Days *</label>
                                <input type="number" name="days" class="form-control" placeholder="e.g., 7" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nights *</label>
                                <input type="number" name="nights" class="form-control" placeholder="e.g., 6" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration Text</label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g., 7 Days / 6 Nights">
                            </div>
                        </div>
                        
                        <!-- Price Details -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price Type *</label>
                                <select name="price_type" class="form-control" required>
                                    <option value="fixed">Fixed Price</option>
                                    <option value="starting_from">Starting From</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (₹) *</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Minimum People *</label>
                                <input type="number" name="min_people" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <!-- Locations Covered -->
                        <div class="mb-3">
                            <label class="form-label">Locations Covered</label>
                            <div id="addPackageLocationsContainer">
                                <div class="feature-input-group input-group">
                                    <input type="text" name="locations_covered[]" class="form-control" placeholder="📍 Paris">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addPackageLocationField()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Add all locations covered in this package</small>
                        </div>
                        
                        <!-- Features -->
                        <div class="mb-3">
                            <label class="form-label">Package Features</label>
                            <div id="addPackageFeaturesContainer">
                                <div class="feature-input-group input-group">
                                    <input type="text" name="features[]" class="form-control" placeholder="✈️ Flight">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addPackageFeatureField()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Add key features of the package</small>
                        </div>
                        
                        <!-- Inclusions -->
                        <div class="mb-3">
                            <label class="form-label">Inclusions</label>
                            <div id="addPackageInclusionsContainer">
                                <div class="feature-input-group input-group">
                                    <input type="text" name="inclusions[]" class="form-control" placeholder="✓ Hotel Accommodation">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addPackageInclusionField()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">What's included in the package</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_package" class="btn-submit">Add Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Tour Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editPackageId">
                        
                        <!-- Basic Info -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Package Title *</label>
                                <input type="text" name="title" id="editPackageTitle" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Destination *</label>
                                <select name="destination_id" id="editPackageDestination" class="form-control" required>
                                    <option value="">Select Destination</option>
                                    <?php foreach($active_destinations as $dest): ?>
                                        <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Package Type & Status -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Package Type *</label>
                                <select name="type" id="editPackageType" class="form-control" required>
                                    <option value="National">National</option>
                                    <option value="International">International</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="editPackageStatus" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Package Image</label>
                                <input type="file" name="image" class="form-control">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                        </div>
                        
                        <!-- Duration Details -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Days *</label>
                                <input type="number" name="days" id="editPackageDays" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nights *</label>
                                <input type="number" name="nights" id="editPackageNights" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration Text</label>
                                <input type="text" name="duration" id="editPackageDuration" class="form-control">
                            </div>
                        </div>
                        
                        <!-- Price Details -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price Type *</label>
                                <select name="price_type" id="editPackagePriceType" class="form-control" required>
                                    <option value="fixed">Fixed Price</option>
                                    <option value="starting_from">Starting From</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (₹) *</label>
                                <input type="number" name="price" id="editPackagePrice" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Minimum People *</label>
                                <input type="number" name="min_people" id="editPackageMinPeople" class="form-control" min="1" required>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editPackageDescription" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <!-- Locations Covered -->
                        <div class="mb-3">
                            <label class="form-label">Locations Covered</label>
                            <div id="editPackageLocationsContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditPackageLocationField()">
                                <i class="fas fa-plus"></i> Add Another Location
                            </button>
                        </div>
                        
                        <!-- Features -->
                        <div class="mb-3">
                            <label class="form-label">Package Features</label>
                            <div id="editPackageFeaturesContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditPackageFeatureField()">
                                <i class="fas fa-plus"></i> Add Another Feature
                            </button>
                        </div>
                        
                        <!-- Inclusions -->
                        <div class="mb-3">
                            <label class="form-label">Inclusions</label>
                            <div id="editPackageInclusionsContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditPackageInclusionField()">
                                <i class="fas fa-plus"></i> Add Another Inclusion
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_package" class="btn-submit">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hotel Name *</label>
                                <input type="text" name="hotel_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Destination *</label>
                                <select name="destination_id" class="form-control" required>
                                    <option value="">Select Destination</option>
                                    <?php foreach($active_destinations as $dest): ?>
                                        <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-control" required>
                                    <option value="Luxury">Luxury</option>
                                    <option value="Beachfront">Beachfront</option>
                                    <option value="Mountain">Mountain</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price/Night *</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image *</label>
                            <input type="file" name="hotel_image" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Features</label>
                            <div id="hotelFeaturesContainer">
                                <div class="feature-input-group input-group">
                                    <input type="text" name="features[]" class="form-control" placeholder="📶 Wifi">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addHotelFeatureField()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Add multiple features for the hotel</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_hotel" class="btn-submit">Add Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Destination Modal -->
    <div class="modal fade" id="addDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Popular Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="addDestinationTitle" class="form-control" required 
                                   placeholder="e.g., Paris, France">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Slug *</label>
                            <div class="input-group">
                                <input type="text" name="slug" id="addDestinationSlug" class="form-control" required 
                                       placeholder="e.g., paris-france">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSlugFromTitle('add')">
                                    Generate
                                </button>
                            </div>
                            <small class="text-muted">URL-friendly version of the title</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image *</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Recommended: Square or landscape images (e.g., 400x300px)</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" value="0" min="0">
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_destination" class="btn-submit">Add Destination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Carousel Modal -->
    <div class="modal fade" id="addCarouselModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Carousel Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Main Image (Large) *</label>
                                <input type="file" name="carousel_image" class="form-control" accept="image/*" required>
                                <small class="text-muted">Recommended size: 1920x1080px (will be used as background)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thumbnail Image *</label>
                                <input type="file" name="thumbnail_image" class="form-control" accept="image/*" required>
                                <small class="text-muted">Recommended size: 150x100px (for thumbnails)</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Beautiful Beach Destination">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alt Text *</label>
                                <input type="text" name="alt_text" class="form-control" required placeholder="Tropical beach with palm trees">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this destination"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" value="0" min="0">
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_carousel_image" class="btn-submit">Add Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Carousel Modal -->
    <div class="modal fade" id="editCarouselModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Carousel Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCarouselId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Main Image</label>
                                <input type="file" name="carousel_image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thumbnail Image</label>
                                <input type="file" name="thumbnail_image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current thumbnail</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image Title</label>
                                <input type="text" name="title" id="editCarouselTitle" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alt Text *</label>
                                <input type="text" name="alt_text" id="editCarouselAlt" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editCarouselDescription" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" id="editCarouselOrder" class="form-control" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" id="editCarouselStatus" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_carousel_image" class="btn-submit">Update Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View Enquiry Modal -->
    <div class="modal fade" id="viewEnquiryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Enquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="enquiry-detail-label">Name:</div>
                    <div class="enquiry-detail-value" id="viewName"></div>
                    
                    <div class="enquiry-detail-label">Email:</div>
                    <div class="enquiry-detail-value" id="viewEmail"></div>
                    
                    <div class="enquiry-detail-label">Phone:</div>
                    <div class="enquiry-detail-value" id="viewPhone"></div>
                    
                    <div class="enquiry-detail-label">Package:</div>
                    <div class="enquiry-detail-value" id="viewPackage"></div>
                    
                    <div class="enquiry-detail-label">Travel Date:</div>
                    <div class="enquiry-detail-value" id="viewTravelDate"></div>
                    
                    <div class="enquiry-detail-label">Number of Travelers:</div>
                    <div class="enquiry-detail-value" id="viewTravelers"></div>
                    
                    <div class="enquiry-detail-label">Message:</div>
                    <div class="enquiry-detail-value" id="viewMessage"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="enquiry-detail-label">Source:</div>
                            <div class="enquiry-detail-value" id="viewSource"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="enquiry-detail-label">Status:</div>
                            <div class="enquiry-detail-value" id="viewStatus"></div>
                        </div>
                    </div>
                    
                    <div class="enquiry-detail-label">Submitted On:</div>
                    <div class="enquiry-detail-value" id="viewCreated"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Other Service Modal -->
    <div class="modal fade" id="viewOtherServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Service Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="enquiry-detail-label">Name:</div>
                    <div class="enquiry-detail-value" id="viewServiceName"></div>
                    
                    <div class="enquiry-detail-label">Email:</div>
                    <div class="enquiry-detail-value" id="viewServiceEmail"></div>
                    
                    <div class="enquiry-detail-label">Phone:</div>
                    <div class="enquiry-detail-value" id="viewServicePhone"></div>
                    
                    <div class="enquiry-detail-label">Service/Package:</div>
                    <div class="enquiry-detail-value" id="viewServicePackage"></div>
                    
                    <div class="enquiry-detail-label">Travel Date:</div>
                    <div class="enquiry-detail-value" id="viewServiceTravelDate"></div>
                    
                    <div class="enquiry-detail-label">Number of Travelers:</div>
                    <div class="enquiry-detail-value" id="viewServiceTravelers"></div>
                    
                    <div class="enquiry-detail-label">Message:</div>
                    <div class="enquiry-detail-value" id="viewServiceMessage"></div>
                    
                    <div class="enquiry-detail-label">Status:</div>
                    <div class="enquiry-detail-value" id="viewServiceStatus"></div>
                    
                    <div class="enquiry-detail-label">Submitted On:</div>
                    <div class="enquiry-detail-value" id="viewServiceCreated"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Hotel Enquiry Modal -->
    <div class="modal fade" id="viewHotelEnquiryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-bed me-2"></i>Hotel Enquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="enquiry-detail-label">Hotel Name:</div>
                            <div class="enquiry-detail-value" id="viewHotelName"></div>
                            
                            <div class="enquiry-detail-label">Destination:</div>
                            <div class="enquiry-detail-value" id="viewHotelDestination"></div>
                            
                            <div class="enquiry-detail-label">Guest Name:</div>
                            <div class="enquiry-detail-value" id="viewHotelGuestName"></div>
                            
                            <div class="enquiry-detail-label">Email:</div>
                            <div class="enquiry-detail-value" id="viewHotelEmail"></div>
                            
                            <div class="enquiry-detail-label">Phone:</div>
                            <div class="enquiry-detail-value" id="viewHotelPhone"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="enquiry-detail-label">Check-In Date:</div>
                            <div class="enquiry-detail-value" id="viewHotelCheckIn"></div>
                            
                            <div class="enquiry-detail-label">Check-Out Date:</div>
                            <div class="enquiry-detail-value" id="viewHotelCheckOut"></div>
                            
                            <div class="enquiry-detail-label">Guests:</div>
                            <div class="enquiry-detail-value" id="viewHotelGuests"></div>
                            
                            <div class="enquiry-detail-label">Rooms:</div>
                            <div class="enquiry-detail-value" id="viewHotelRooms"></div>
                        </div>
                    </div>
                    
                    <div class="enquiry-detail-label mt-3">Message:</div>
                    <div class="enquiry-detail-value" id="viewHotelMessage" style="white-space: pre-line;"></div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="enquiry-detail-label">Source:</div>
                            <div class="enquiry-detail-value" id="viewHotelSource"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="enquiry-detail-label">Status:</div>
                            <div class="enquiry-detail-value" id="viewHotelStatus"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="enquiry-detail-label">Submitted On:</div>
                            <div class="enquiry-detail-value" id="viewHotelCreated"></div>
                        </div>
                    </div>
                    
                    <div class="enquiry-detail-label">Last Updated:</div>
                    <div class="enquiry-detail-value" id="viewHotelUpdated"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Hotel Enquiries Modal -->
    <div class="modal fade" id="exportHotelEnquiriesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-download me-2"></i>Export Hotel Enquiries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?section=hotel_enquiries">
                    <div class="modal-body">
                        <p>Select filters for export (leave all empty to export all enquiries):</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Filter by Hotel</label>
                            <select name="hotel_filter_export" class="form-control">
                                <option value="">All Hotels</option>
                                <?php foreach($all_hotels as $hotel): ?>
                                    <option value="<?= $hotel['id'] ?>"><?= htmlspecialchars($hotel['hotel_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Filter by Status</label>
                            <select name="hotel_status_filter_export" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="New">New</option>
                                <option value="Read">Read</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Closed">Closed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="hotel_date_from_export" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="hotel_date_to_export" class="form-control">
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            The export will be downloaded as a CSV file compatible with Excel.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="export_hotel_enquiries" class="btn btn-success">
                            <i class="fas fa-download"></i> Download CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Toggle sidebar on mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            if ($('#packagesTable').length) {
                $('#packagesTable').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }
            
            if ($('#hotelsTable').length) {
                $('#hotelsTable').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }
            
            if ($('#destinationsTable').length) {
                $('#destinationsTable').DataTable({
                    pageLength: 25,
                    order: [[4, 'asc']]
                });
            }
            
            if ($('#enquiriesTable').length) {
                $('#enquiriesTable').DataTable({
                    pageLength: 50,
                    order: [[9, 'desc']]
                });
            }
            
            if ($('#otherServicesTable').length) {
                $('#otherServicesTable').DataTable({
                    pageLength: 50,
                    order: [[7, 'desc']]
                });
            }
            
            if ($('#hotelEnquiriesTable').length) {
                $('#hotelEnquiriesTable').DataTable({
                    pageLength: 50,
                    order: [[8, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: [0, 9] }
                    ]
                });
            }
        });
        
        // Feature field functions
        function addPackageFeatureField() {
            const container = document.getElementById('addPackageFeaturesContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addPackageLocationField() {
            const container = document.getElementById('addPackageLocationsContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="locations_covered[]" class="form-control" placeholder="📍 Enter location">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addPackageInclusionField() {
            const container = document.getElementById('addPackageInclusionsContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="inclusions[]" class="form-control" placeholder="✓ Enter inclusion">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addHotelFeatureField() {
            const container = document.getElementById('hotelFeaturesContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addEditHotelFeatureField() {
            const container = document.getElementById('editHotelFeaturesContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addEditPackageFeatureField() {
            const container = document.getElementById('editPackageFeaturesContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addEditPackageLocationField() {
            const container = document.getElementById('editPackageLocationsContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="locations_covered[]" class="form-control" placeholder="📍 Enter location">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function addEditPackageInclusionField() {
            const container = document.getElementById('editPackageInclusionsContainer');
            const div = document.createElement('div');
            div.className = 'feature-input-group input-group mt-2';
            div.innerHTML = `
                <input type="text" name="inclusions[]" class="form-control" placeholder="✓ Enter inclusion">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeatureField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function removeFeatureField(button) {
            button.parentElement.remove();
        }
        
        // Generate slug from title
        function generateSlugFromTitle(type) {
            let titleInput, slugInput;
            
            if (type === 'add') {
                titleInput = document.getElementById('addDestinationTitle');
                slugInput = document.getElementById('addDestinationSlug');
            } else {
                titleInput = document.getElementById('editDestinationTitle');
                slugInput = document.getElementById('editDestinationSlug');
            }
            
            if (titleInput && slugInput) {
                const title = titleInput.value;
                if (title) {
                    let slug = title.toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/--+/g, '-')
                        .replace(/^-+/, '')
                        .replace(/-+$/, '');
                    
                    slugInput.value = slug;
                }
            }
        }
        
        // Select all checkboxes
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.enquiry-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        document.getElementById('selectAllHotel')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.hotel-enquiry-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Confirm bulk actions
        function confirmBulkAction() {
            const action = document.querySelector('select[name="bulk_action"]').value;
            const selected = document.querySelectorAll('.enquiry-checkbox:checked').length;
            
            if (selected === 0) {
                alert('Please select at least one enquiry.');
                return false;
            }
            
            if (action === 'delete_selected') {
                return confirm('Are you sure you want to delete the selected enquiries?');
            }
            
            return true;
        }
        
        function confirmBulkHotelAction() {
            const action = document.querySelector('select[name="bulk_hotel_action"]').value;
            const selected = document.querySelectorAll('.hotel-enquiry-checkbox:checked').length;
            
            if (selected === 0) {
                alert('Please select at least one enquiry.');
                return false;
            }
            
            if (action === 'delete_selected') {
                return confirm('Are you sure you want to delete the selected hotel enquiries?');
            }
            
            return true;
        }
        
        // Populate modal data
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Package Modal
            const editPackageModal = document.getElementById('editPackageModal');
            if (editPackageModal) {
                editPackageModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('editPackageId').value = button.getAttribute('data-id');
                    document.getElementById('editPackageTitle').value = button.getAttribute('data-title');
                    document.getElementById('editPackageDestination').value = button.getAttribute('data-destination');
                    document.getElementById('editPackageType').value = button.getAttribute('data-type');
                    document.getElementById('editPackagePrice').value = button.getAttribute('data-price');
                    document.getElementById('editPackagePriceType').value = button.getAttribute('data-price_type') || 'fixed';
                    document.getElementById('editPackageMinPeople').value = button.getAttribute('data-min_people') || '1';
                    document.getElementById('editPackageDuration').value = button.getAttribute('data-duration');
                    document.getElementById('editPackageDays').value = button.getAttribute('data-days') || '';
                    document.getElementById('editPackageNights').value = button.getAttribute('data-nights') || '';
                    document.getElementById('editPackageStatus').value = button.getAttribute('data-status');
                    document.getElementById('editPackageDescription').value = button.getAttribute('data-description');
                    
                    // Populate locations
                    const locationsContainer = document.getElementById('editPackageLocationsContainer');
                    locationsContainer.innerHTML = '';
                    const locations = JSON.parse(button.getAttribute('data-locations') || '[]');
                    
                    if (locations.length === 0) {
                        locationsContainer.innerHTML = `
                            <div class="feature-input-group input-group">
                                <input type="text" name="locations_covered[]" class="form-control" placeholder="📍 Paris">
                                <button type="button" class="btn btn-outline-secondary" onclick="addEditPackageLocationField()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        locations.forEach((location, index) => {
                            const div = document.createElement('div');
                            div.className = 'feature-input-group input-group' + (index > 0 ? ' mt-2' : '');
                            div.innerHTML = `
                                <input type="text" name="locations_covered[]" class="form-control" value="${location.replace(/"/g, '&quot;')}">
                                <button type="button" class="btn ${index === 0 ? 'btn-outline-secondary' : 'btn-outline-danger'}" 
                                        onclick="${index === 0 ? 'addEditPackageLocationField()' : 'removeFeatureField(this)'}">
                                    <i class="fas ${index === 0 ? 'fa-plus' : 'fa-minus'}"></i>
                                </button>
                            `;
                            locationsContainer.appendChild(div);
                        });
                    }
                    
                    // Populate features
                    const featuresContainer = document.getElementById('editPackageFeaturesContainer');
                    featuresContainer.innerHTML = '';
                    const features = JSON.parse(button.getAttribute('data-features') || '[]');
                    
                    if (features.length === 0) {
                        featuresContainer.innerHTML = `
                            <div class="feature-input-group input-group">
                                <input type="text" name="features[]" class="form-control" placeholder="✈️ Flight">
                                <button type="button" class="btn btn-outline-secondary" onclick="addEditPackageFeatureField()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        features.forEach((feature, index) => {
                            const div = document.createElement('div');
                            div.className = 'feature-input-group input-group' + (index > 0 ? ' mt-2' : '');
                            div.innerHTML = `
                                <input type="text" name="features[]" class="form-control" value="${feature.replace(/"/g, '&quot;')}">
                                <button type="button" class="btn ${index === 0 ? 'btn-outline-secondary' : 'btn-outline-danger'}" 
                                        onclick="${index === 0 ? 'addEditPackageFeatureField()' : 'removeFeatureField(this)'}">
                                    <i class="fas ${index === 0 ? 'fa-plus' : 'fa-minus'}"></i>
                                </button>
                            `;
                            featuresContainer.appendChild(div);
                        });
                    }
                    
                    // Populate inclusions
                    const inclusionsContainer = document.getElementById('editPackageInclusionsContainer');
                    inclusionsContainer.innerHTML = '';
                    const inclusions = JSON.parse(button.getAttribute('data-inclusions') || '[]');
                    
                    if (inclusions.length === 0) {
                        inclusionsContainer.innerHTML = `
                            <div class="feature-input-group input-group">
                                <input type="text" name="inclusions[]" class="form-control" placeholder="✓ Hotel Accommodation">
                                <button type="button" class="btn btn-outline-secondary" onclick="addEditPackageInclusionField()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        inclusions.forEach((inclusion, index) => {
                            const div = document.createElement('div');
                            div.className = 'feature-input-group input-group' + (index > 0 ? ' mt-2' : '');
                            div.innerHTML = `
                                <input type="text" name="inclusions[]" class="form-control" value="${inclusion.replace(/"/g, '&quot;')}">
                                <button type="button" class="btn ${index === 0 ? 'btn-outline-secondary' : 'btn-outline-danger'}" 
                                        onclick="${index === 0 ? 'addEditPackageInclusionField()' : 'removeFeatureField(this)'}">
                                    <i class="fas ${index === 0 ? 'fa-plus' : 'fa-minus'}"></i>
                                </button>
                            `;
                            inclusionsContainer.appendChild(div);
                        });
                    }
                });
            }
            
            // Edit Hotel Modal
            const editHotelModal = document.getElementById('editHotelModal');
            if (editHotelModal) {
                editHotelModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('editHotelId').value = button.getAttribute('data-id');
                    document.getElementById('editHotelName').value = button.getAttribute('data-name');
                    document.getElementById('editHotelDestination').value = button.getAttribute('data-destination');
                    document.getElementById('editHotelCategory').value = button.getAttribute('data-category');
                    document.getElementById('editHotelPrice').value = button.getAttribute('data-price');
                    document.getElementById('editHotelStatus').value = button.getAttribute('data-status');
                    document.getElementById('editHotelDescription').value = button.getAttribute('data-description');
                    
                    // Populate features
                    const featuresContainer = document.getElementById('editHotelFeaturesContainer');
                    featuresContainer.innerHTML = '';
                    const features = JSON.parse(button.getAttribute('data-features') || '[]');
                    
                    if (features.length === 0) {
                        featuresContainer.innerHTML = `
                            <div class="feature-input-group input-group">
                                <input type="text" name="features[]" class="form-control" placeholder="📶 Wifi">
                                <button type="button" class="btn btn-outline-secondary" onclick="addEditHotelFeatureField()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        features.forEach((feature, index) => {
                            const div = document.createElement('div');
                            div.className = 'feature-input-group input-group' + (index > 0 ? ' mt-2' : '');
                            div.innerHTML = `
                                <input type="text" name="features[]" class="form-control" value="${feature.replace(/"/g, '&quot;')}">
                                <button type="button" class="btn ${index === 0 ? 'btn-outline-secondary' : 'btn-outline-danger'}" 
                                        onclick="${index === 0 ? 'addEditHotelFeatureField()' : 'removeFeatureField(this)'}">
                                    <i class="fas ${index === 0 ? 'fa-plus' : 'fa-minus'}"></i>
                                </button>
                            `;
                            featuresContainer.appendChild(div);
                        });
                    }
                });
            }
            
            // Edit Destination Modal
            const editDestinationModal = document.getElementById('editDestinationModal');
            if (editDestinationModal) {
                editDestinationModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('editDestinationId').value = button.getAttribute('data-id');
                    document.getElementById('editDestinationTitle').value = button.getAttribute('data-title');
                    document.getElementById('editDestinationSlug').value = button.getAttribute('data-slug');
                    document.getElementById('editDestinationStatus').value = button.getAttribute('data-status');
                    document.getElementById('editDestinationOrder').value = button.getAttribute('data-order');
                });
            }
            
            // Edit Carousel Modal
            const editCarouselModal = document.getElementById('editCarouselModal');
            if (editCarouselModal) {
                editCarouselModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('editCarouselId').value = button.getAttribute('data-id');
                    document.getElementById('editCarouselTitle').value = button.getAttribute('data-title');
                    document.getElementById('editCarouselAlt').value = button.getAttribute('data-alt');
                    document.getElementById('editCarouselDescription').value = button.getAttribute('data-description');
                    document.getElementById('editCarouselOrder').value = button.getAttribute('data-order');
                    document.getElementById('editCarouselStatus').value = button.getAttribute('data-status');
                });
            }
            
            // View Enquiry Modal
            const viewEnquiryModal = document.getElementById('viewEnquiryModal');
            if (viewEnquiryModal) {
                viewEnquiryModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('viewName').textContent = button.getAttribute('data-name');
                    document.getElementById('viewEmail').textContent = button.getAttribute('data-email');
                    document.getElementById('viewPhone').textContent = button.getAttribute('data-phone');
                    document.getElementById('viewPackage').textContent = button.getAttribute('data-package');
                    document.getElementById('viewTravelDate').textContent = button.getAttribute('data-traveldate');
                    document.getElementById('viewTravelers').textContent = button.getAttribute('data-travelers');
                    document.getElementById('viewMessage').textContent = button.getAttribute('data-message');
                    document.getElementById('viewSource').textContent = button.getAttribute('data-source');
                    
                    const status = button.getAttribute('data-status');
                    const statusSpan = document.getElementById('viewStatus');
                    statusSpan.textContent = status;
                    statusSpan.className = 'enquiry-detail-value';
                    
                    if (status === 'New') statusSpan.classList.add('bg-danger', 'text-white');
                    else if (status === 'In Progress') statusSpan.classList.add('bg-warning');
                    else if (status === 'Closed') statusSpan.classList.add('bg-success', 'text-white');
                    
                    document.getElementById('viewCreated').textContent = button.getAttribute('data-created');
                });
            }
            
            // View Other Service Modal
            const viewOtherServiceModal = document.getElementById('viewOtherServiceModal');
            if (viewOtherServiceModal) {
                viewOtherServiceModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('viewServiceName').textContent = button.getAttribute('data-name');
                    document.getElementById('viewServiceEmail').textContent = button.getAttribute('data-email');
                    document.getElementById('viewServicePhone').textContent = button.getAttribute('data-phone');
                    document.getElementById('viewServicePackage').textContent = button.getAttribute('data-package');
                    document.getElementById('viewServiceTravelDate').textContent = button.getAttribute('data-traveldate');
                    document.getElementById('viewServiceTravelers').textContent = button.getAttribute('data-travelers');
                    document.getElementById('viewServiceMessage').textContent = button.getAttribute('data-message');
                    
                    const status = button.getAttribute('data-status');
                    const statusSpan = document.getElementById('viewServiceStatus');
                    statusSpan.textContent = status;
                    statusSpan.className = 'enquiry-detail-value';
                    
                    if (status === 'New') statusSpan.classList.add('bg-danger', 'text-white');
                    else if (status === 'In Progress') statusSpan.classList.add('bg-warning');
                    else if (status === 'Closed') statusSpan.classList.add('bg-success', 'text-white');
                    
                    document.getElementById('viewServiceCreated').textContent = button.getAttribute('data-created');
                });
            }
            
            // View Hotel Enquiry Modal
            const viewHotelEnquiryModal = document.getElementById('viewHotelEnquiryModal');
            if (viewHotelEnquiryModal) {
                viewHotelEnquiryModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    
                    document.getElementById('viewHotelName').textContent = button.getAttribute('data-hotel');
                    document.getElementById('viewHotelDestination').textContent = button.getAttribute('data-destination');
                    document.getElementById('viewHotelGuestName').textContent = button.getAttribute('data-name');
                    document.getElementById('viewHotelEmail').textContent = button.getAttribute('data-email');
                    document.getElementById('viewHotelPhone').textContent = button.getAttribute('data-phone');
                    document.getElementById('viewHotelCheckIn').textContent = button.getAttribute('data-checkin');
                    document.getElementById('viewHotelCheckOut').textContent = button.getAttribute('data-checkout');
                    document.getElementById('viewHotelGuests').textContent = button.getAttribute('data-guests');
                    document.getElementById('viewHotelRooms').textContent = button.getAttribute('data-rooms');
                    document.getElementById('viewHotelMessage').textContent = button.getAttribute('data-message');
                    document.getElementById('viewHotelSource').textContent = button.getAttribute('data-source');
                    
                    const status = button.getAttribute('data-status');
                    const statusSpan = document.getElementById('viewHotelStatus');
                    statusSpan.textContent = status;
                    statusSpan.className = 'enquiry-detail-value';
                    
                    statusSpan.classList.remove('bg-danger', 'bg-warning', 'bg-success', 'bg-secondary', 'text-white');
                    if (status === 'New') statusSpan.classList.add('bg-danger', 'text-white');
                    else if (status === 'In Progress') statusSpan.classList.add('bg-warning');
                    else if (status === 'Closed') statusSpan.classList.add('bg-success', 'text-white');
                    else if (status === 'Cancelled') statusSpan.classList.add('bg-secondary', 'text-white');
                    
                    document.getElementById('viewHotelCreated').textContent = button.getAttribute('data-created');
                    document.getElementById('viewHotelUpdated').textContent = button.getAttribute('data-updated');
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        
        // Export enquiries as CSV
        function exportEnquiries() {
            const table = document.getElementById('enquiriesTable');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                cols.forEach((col, index) => {
                    if (index > 0) {
                        let text = col.innerText.replace(/,/g, ';');
                        rowData.push('"' + text + '"');
                    }
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'enquiries_export_' + new Date().toISOString().slice(0,10) + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
    <!-- Export Contact Messages Modal -->
<div class="modal fade" id="exportContactMessagesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-download me-2"></i>Export Contact Messages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?section=contact_messages">
                <div class="modal-body">
                    <p>Select filters for export (leave all empty to export all messages):</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Filter by Status</label>
                        <select name="contact_filter_status_export" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="New">New</option>
                            <option value="Read">Read</option>
                            <option value="Replied">Replied</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="contact_date_from_export" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="contact_date_to_export" class="form-control">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The export will be downloaded as a CSV file compatible with Excel.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="export_contact_messages" class="btn btn-success">
                        <i class="fas fa-download"></i> Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Contact Messages specific functions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for contact messages
    if ($('#contactMessagesTable').length) {
        $('#contactMessagesTable').DataTable({
            pageLength: 50,
            order: [[8, 'desc']], // Order by date
            columnDefs: [
                { orderable: false, targets: [0, 9] } // Disable ordering on checkbox and actions columns
            ]
        });
    }
    
    // Select all checkboxes for contact messages
    document.getElementById('selectAllContact')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.contact-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // View Contact Message Modal
    const viewContactModal = document.getElementById('viewContactMessageModal');
    if (viewContactModal) {
        viewContactModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            document.getElementById('viewContactName').textContent = button.getAttribute('data-name');
            document.getElementById('viewContactEmail').textContent = button.getAttribute('data-email');
            document.getElementById('viewContactPhone').textContent = button.getAttribute('data-phone');
            document.getElementById('viewContactSubject').textContent = button.getAttribute('data-subject');
            document.getElementById('viewContactMessage').textContent = button.getAttribute('data-message');
            document.getElementById('viewContactIP').textContent = button.getAttribute('data-ip');
            document.getElementById('viewContactUserAgent').textContent = button.getAttribute('data-useragent');
            
            const status = button.getAttribute('data-status');
            const statusSpan = document.getElementById('viewContactStatus');
            statusSpan.textContent = status;
            statusSpan.className = 'enquiry-detail-value';
            
            statusSpan.classList.remove('bg-danger', 'bg-warning', 'bg-success', 'bg-secondary', 'bg-info', 'text-white');
            if (status === 'New') statusSpan.classList.add('bg-danger', 'text-white');
            else if (status === 'Read') statusSpan.classList.add('bg-secondary', 'text-white');
            else if (status === 'Replied') statusSpan.classList.add('bg-info', 'text-white');
            else if (status === 'Archived') statusSpan.classList.add('bg-dark', 'text-white');
            
            document.getElementById('viewContactCreated').textContent = button.getAttribute('data-created');
            document.getElementById('viewContactUpdated').textContent = button.getAttribute('data-updated');
        });
    }
});

// Confirm bulk action for contact messages
function confirmBulkContactAction() {
    const action = document.querySelector('select[name="bulk_contact_action"]').value;
    const selected = document.querySelectorAll('.contact-checkbox:checked').length;
    
    if (selected === 0) {
        alert('Please select at least one message.');
        return false;
    }
    
    if (action === 'delete_selected') {
        return confirm('Are you sure you want to delete the selected messages?');
    }
    
    return true;
}
</script>
<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-star me-2"></i>Feedback Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="enquiry-detail-label">Name:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackName"></div>
                        
                        <div class="enquiry-detail-label">Email:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackEmail"></div>
                        
                        <div class="enquiry-detail-label">Phone:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackPhone"></div>
                        
                        <div class="enquiry-detail-label">Subject:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackSubject"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="enquiry-detail-label">Rating:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackRating"></div>
                        
                        <div class="enquiry-detail-label">Status:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackStatus"></div>
                        
                        <div class="enquiry-detail-label">IP Address:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackIP"></div>
                        
                        <div class="enquiry-detail-label">Page URL:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackPage"></div>
                    </div>
                </div>
                
                <div class="enquiry-detail-label mt-3">Message:</div>
                <div class="enquiry-detail-value" id="viewFeedbackMessage" style="white-space: pre-line;"></div>
                
                <div class="enquiry-detail-label mt-3">Admin Notes:</div>
                <div class="enquiry-detail-value" id="viewFeedbackNotes" style="white-space: pre-line;"></div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="enquiry-detail-label">User Agent:</div>
                        <div class="enquiry-detail-value small" id="viewFeedbackUserAgent"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="enquiry-detail-label">Created:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackCreated"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="enquiry-detail-label">Updated:</div>
                        <div class="enquiry-detail-value" id="viewFeedbackUpdated"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Feedback Notes Modal -->
<div class="modal fade" id="editFeedbackNotesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Admin Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editNotesId">
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" id="editNotesContent" class="form-control" rows="5" placeholder="Add your private notes about this feedback..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_feedback_notes" class="btn btn-primary">Save Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Feedbacks Modal -->
<div class="modal fade" id="exportFeedbacksModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-download me-2"></i>Export Feedbacks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?section=feedbacks">
                <div class="modal-body">
                    <p>Select filters for export:</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Filter by Status</label>
                        <select name="feedback_filter_status_export" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="New">New</option>
                            <option value="Published">Published</option>
                            <option value="Pending">Pending</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Filter by Rating</label>
                        <select name="feedback_filter_rating_export" class="form-control">
                            <option value="">All Ratings</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="feedback_date_from_export" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="feedback_date_to_export" class="form-control">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Export will be downloaded as a CSV file.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="export_feedbacks" class="btn btn-success">
                        <i class="fas fa-download"></i> Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Feedback specific functions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for feedbacks
    if ($('#feedbacksTable').length) {
        $('#feedbacksTable').DataTable({
            pageLength: 50,
            order: [[9, 'desc']], // Order by date
            columnDefs: [
                { orderable: false, targets: [0, 10] } // Disable ordering on checkbox and actions columns
            ]
        });
    }
    
    // Select all checkboxes for feedbacks
    document.getElementById('selectAllFeedback')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.feedback-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // View Feedback Modal
    const viewFeedbackModal = document.getElementById('viewFeedbackModal');
    if (viewFeedbackModal) {
        viewFeedbackModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            document.getElementById('viewFeedbackName').textContent = button.getAttribute('data-name');
            document.getElementById('viewFeedbackEmail').textContent = button.getAttribute('data-email');
            document.getElementById('viewFeedbackPhone').textContent = button.getAttribute('data-phone');
            document.getElementById('viewFeedbackSubject').textContent = button.getAttribute('data-subject');
            document.getElementById('viewFeedbackMessage').textContent = button.getAttribute('data-message');
            document.getElementById('viewFeedbackIP').textContent = button.getAttribute('data-ip');
            document.getElementById('viewFeedbackPage').textContent = button.getAttribute('data-page');
            document.getElementById('viewFeedbackUserAgent').textContent = button.getAttribute('data-useragent');
            document.getElementById('viewFeedbackNotes').textContent = button.getAttribute('data-notes');
            
            const rating = button.getAttribute('data-rating');
            const ratingSpan = document.getElementById('viewFeedbackRating');
            if (rating !== 'Not rated') {
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= parseInt(rating)) {
                        stars += '<i class="fas fa-star text-warning"></i>';
                    } else {
                        stars += '<i class="far fa-star text-warning"></i>';
                    }
                }
                ratingSpan.innerHTML = stars + ' (' + rating + ' Star)';
            } else {
                ratingSpan.textContent = 'Not rated';
            }
            
            const status = button.getAttribute('data-status');
            const statusSpan = document.getElementById('viewFeedbackStatus');
            statusSpan.textContent = status;
            statusSpan.className = 'enquiry-detail-value';
            
            statusSpan.classList.remove('bg-danger', 'bg-success', 'bg-warning', 'bg-secondary', 'bg-info', 'text-white');
            if (status === 'New') statusSpan.classList.add('bg-danger', 'text-white');
            else if (status === 'Published') statusSpan.classList.add('bg-success', 'text-white');
            else if (status === 'Pending') statusSpan.classList.add('bg-warning');
            else if (status === 'Rejected') statusSpan.classList.add('bg-secondary', 'text-white');
            else if (status === 'Archived') statusSpan.classList.add('bg-dark', 'text-white');
            
            document.getElementById('viewFeedbackCreated').textContent = button.getAttribute('data-created');
            document.getElementById('viewFeedbackUpdated').textContent = button.getAttribute('data-updated');
        });
    }
    
    // Edit Notes Modal
    const editNotesModal = document.getElementById('editFeedbackNotesModal');
    if (editNotesModal) {
        editNotesModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('editNotesId').value = button.getAttribute('data-id');
            document.getElementById('editNotesContent').value = button.getAttribute('data-notes');
        });
    }
});

// Confirm bulk action for feedbacks
function confirmBulkFeedbackAction() {
    const action = document.querySelector('select[name="bulk_feedback_action"]').value;
    const selected = document.querySelectorAll('.feedback-checkbox:checked').length;
    
    if (selected === 0) {
        alert('Please select at least one feedback.');
        return false;
    }
    
    if (action === 'delete_selected') {
        return confirm('Are you sure you want to delete the selected feedbacks?');
    }
    
    return true;
}
</script>
</body>
</html>