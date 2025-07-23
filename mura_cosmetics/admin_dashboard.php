<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_product':
            $name = trim($_POST['name']);
            $category_id = $_POST['category_id'];
            $price = $_POST['price'];
            $description = trim($_POST['description']);
            
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    $newname = time() . '_' . $filename;
                    $upload_path = 'uploads/' . $newname;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image = $newname;
                    } else {
                        $error = 'Failed to upload image';
                    }
                } else {
                    $error = 'Invalid image format. Only JPG, JPEG, PNG, GIF allowed';
                }
            }
            
            if (empty($error)) {
                $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, description, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sidss", $name, $category_id, $price, $description, $image);
                
                if ($stmt->execute()) {
                    $message = 'Product added successfully!';
                } else {
                    $error = 'Failed to add product';
                }
            }
            break;
            
        case 'edit_product':
            $id = $_POST['product_id'];
            $name = trim($_POST['name']);
            $category_id = $_POST['category_id'];
            $price = $_POST['price'];
            $description = trim($_POST['description']);
            
            // Handle image upload for edit
            $image_query = "";
            $params = "sids";
            $values = [$name, $category_id, $price, $description];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    $newname = time() . '_' . $filename;
                    $upload_path = 'uploads/' . $newname;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_query = ", image = ?";
                        $params .= "s";
                        $values[] = $newname;
                        
                        // Delete old image
                        $old_image_stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                        $old_image_stmt->bind_param("i", $id);
                        $old_image_stmt->execute();
                        $old_result = $old_image_stmt->get_result();
                        if ($old_row = $old_result->fetch_assoc()) {
                            if ($old_row['image'] && file_exists('uploads/' . $old_row['image'])) {
                                unlink('uploads/' . $old_row['image']);
                            }
                        }
                    }
                }
            }
            
            $values[] = $id;
            $params .= "i";
            
            $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, description = ?" . $image_query . " WHERE id = ?");
            $stmt->bind_param($params, ...$values);
            
            if ($stmt->execute()) {
                $message = 'Product updated successfully!';
            } else {
                $error = 'Failed to update product';
            }
            break;
            
        case 'delete_product':
            $id = $_POST['product_id'];
            
            // Get image filename before deleting
            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Delete image file
                if ($row['image'] && file_exists('uploads/' . $row['image'])) {
                    unlink('uploads/' . $row['image']);
                }
                
                // Delete product from database
                $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $delete_stmt->bind_param("i", $id);
                
                if ($delete_stmt->execute()) {
                    $message = 'Product deleted successfully!';
                } else {
                    $error = 'Failed to delete product';
                }
            }
            break;
    }
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get all products with category names
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");

// Get product for editing if edit_id is set
$edit_product = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $edit_product = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mura Cosmetics</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f7fa;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .dashboard-nav {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .nav-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .nav-btn:hover, .nav-btn.active {
            background: #764ba2;
        }

        .section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }

        .section.active {
            display: block;
        }

        .section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            background: #f9f9f9;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .file-input:hover {
            border-color: #667eea;
        }

        .image-preview {
            margin-top: 10px;
            max-width: 200px;
            border-radius: 5px;
            display: none;
        }

        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #764ba2;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: #f39c12;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            background: #f9f9f9;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .product-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .product-info p {
            color: #666;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 1.2em;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .nav-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Mura Cosmetics - Admin Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Dashboard Navigation -->
        <div class="dashboard-nav">
            <div class="nav-buttons">
                <button class="nav-btn active" onclick="showSection('overview')">Overview</button>
                <button class="nav-btn" onclick="showSection('add-product')">Add Product</button>
                <button class="nav-btn" onclick="showSection('manage-products')">Manage Products</button>
            </div>
        </div>

        <!-- Overview Section -->
        <div id="overview" class="section active">
            <h2>Dashboard Overview</h2>
            
            <div class="stats-grid">
                <?php
                $total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
                $total_categories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
                $recent_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_products; ?></div>
                    <div>Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_categories; ?></div>
                    <div>Categories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $recent_products; ?></div>
                    <div>Added Today</div>
                </div>
            </div>

            <h3>Recent Products</h3>
            <div class="products-grid">
                <?php
                $recent = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 6");
                while ($product = $recent->fetch_assoc()):
                ?>
                <div class="product-card">
                    <?php if ($product['image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="background: #ddd; display: flex; align-items: center; justify-content: center; color: #666;">No Image</div>
                    <?php endif; ?>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></p>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Add Product Section -->
        <div id="add-product" class="section">
            <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
            
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit_product' : 'add_product'; ?>">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $categories->data_seek(0);
                            while ($category = $categories->fetch_assoc()):
                            ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*" class="file-input" onchange="previewImage(this)">
                        <img id="imagePreview" class="image-preview" alt="Preview">
                        <?php if ($edit_product && $edit_product['image']): ?>
                            <div style="margin-top: 10px;">
                                <strong>Current Image:</strong><br>
                                <img src="uploads/<?php echo htmlspecialchars($edit_product['image']); ?>" style="max-width: 200px; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Enter product description..."><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn"><?php echo $edit_product ? 'Update Product' : 'Add Product'; ?></button>
                    <?php if ($edit_product): ?>
                        <a href="admin_dashboard.php" class="btn btn-warning">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Manage Products Section -->
        <div id="manage-products" class="section">
            <h2>Manage Products</h2>
            
            <div class="products-grid">
                <?php
                $products->data_seek(0);
                while ($product = $products->fetch_assoc()):
                ?>
                <div class="product-card">
                    <?php if ($product['image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="background: #ddd; display: flex; align-items: center; justify-content: center; color: #666;">No Image</div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></p>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <?php if ($product['description']): ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                        <?php endif; ?>
                        <p><small><strong>Added:</strong> <?php echo date('M j, Y', strtotime($product['created_at'])); ?></small></p>
                    </div>

                    <div class="product-actions">
                        <a href="?edit_id=<?php echo $product['id']; ?>" class="btn btn-warning" onclick="showSection('add-product')">Edit</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        // Section navigation
        function showSection(sectionId) {
            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));
            
            // Remove active class from all nav buttons
            const navButtons = document.querySelectorAll('.nav-btn');
            navButtons.forEach(btn => btn.classList.remove('active'));
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const category = document.getElementById('category_id').value;
            const price = document.getElementById('price').value;

            if (!name || !category || !price) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                return false;
            }
        });

        // Auto-show edit section if edit_id is present
        <?php if (isset($_GET['edit_id'])): ?>
        showSection('add-product');
        document.querySelector('[onclick="showSection(\'add-product\')"]').classList.add('active');
        document.querySelector('[onclick="showSection(\'overview\')"]').classList.remove('active');
        <?php endif; ?>
    </script>
</body>
</html>