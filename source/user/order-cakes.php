<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require_once("../conn.php");  // Changed to require_once for better error handling

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    // Fetch cakes from the database
    $sql = "SELECT product_id, name, type, base_price, max_tiers, quantity FROM Products WHERE quantity > 0";  // Only show available products
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $cakes = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cakes[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $cakes = [];
}

// Fetch flavors from database instead of hardcoding
try {
    $sql = "SELECT flavor_name FROM Flavors WHERE active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $flavors = [];
    while ($row = $result->fetch_assoc()) {
        $flavors[] = $row['flavor_name'];
    }
} catch (Exception $e) {
    error_log("Error fetching flavors: " . $e->getMessage());
    $flavors = ['Chocolate', 'Vanilla', 'Strawberry'];  // Fallback flavors
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carlyn Cake Shop - Order Cakes</title>
    <meta name="description" content="Order custom cakes from Carlyn Cake Shop">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/order-cakes.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Carlyn Cake Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="nav-link btn btn-link text-white position-relative" data-bs-toggle="modal" data-bs-target="#cartModal">
                            Cart
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center mb-4" style="color: #d6336c;">Our Cakes</h1>
        <div class="row" id="cakes-container">
            <?php if (!empty($cakes)): ?>
                <?php foreach ($cakes as $cake): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card cake-card h-100">
                            <img 
                                src="images/cake-placeholder.jpg" 
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($cake['name']); ?>"
                                loading="lazy"
                            >
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title" style="color: #d6336c;"><?php echo htmlspecialchars($cake['name']); ?></h5>
                                <p class="card-text">Type: <?php echo htmlspecialchars($cake['type']); ?></p>
                                <p class="card-text">Base Price: $<?php echo number_format($cake['base_price'], 2); ?></p>
                                <p class="card-text">Max Tiers: <?php echo htmlspecialchars($cake['max_tiers']); ?></p>
                                <?php if ($cake['quantity'] > 0): ?>
                                    <p class="card-text text-success">In Stock (<?php echo $cake['quantity']; ?> available)</p>
                                    <button class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#cakeModal<?php echo $cake['product_id']; ?>">
                                        View Details
                                    </button>
                                <?php else: ?>
                                    <p class="card-text text-danger">Out of Stock</p>
                                    <button class="btn btn-secondary mt-auto" disabled>
                                        Unavailable
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Cake Details -->
                    <div class="modal fade" id="cakeModal<?php echo $cake['product_id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo htmlspecialchars($cake['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <img 
                                        src="<?php echo htmlspecialchars($cake['image_url'] ?? 'images/cake-placeholder.jpg'); ?>" 
                                        class="img-fluid mb-3"
                                        alt="<?php echo htmlspecialchars($cake['name']); ?>"
                                    >
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($cake['type']); ?></p>
                                    <p><strong>Base Price:</strong> $<?php echo number_format($cake['base_price'], 2); ?></p>
                                    <p><strong>Max Tiers:</strong> <?php echo htmlspecialchars($cake['max_tiers']); ?></p>
                                    <p><?php echo htmlspecialchars($cake['description'] ?? 'No description available.'); ?></p>
                                    <p>Would you like to order this cake as is or customize it further?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary add-to-cart" 
                                            data-product-id="<?php echo $cake['product_id']; ?>" 
                                            data-customized="0">
                                        Add to Cart
                                    </button>
                                    <button class="btn btn-success" data-bs-toggle="modal" 
                                            data-bs-target="#customizeModal<?php echo $cake['product_id']; ?>"
                                            data-bs-dismiss="modal">
                                        Customize
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customize Modal -->
                    <div class="modal fade" id="customizeModal<?php echo $cake['product_id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Customize <?php echo htmlspecialchars($cake['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="customizeForm<?php echo $cake['product_id']; ?>" class="customize-form" novalidate>
                                        <input type="hidden" name="product_id" value="<?php echo $cake['product_id']; ?>">
                                        <input type="hidden" name="customized" value="1">

                                        <div class="mb-3">
                                            <label for="tiers<?php echo $cake['product_id']; ?>" class="form-label">Number of Tiers</label>
                                            <input type="number" 
                                                   id="tiers<?php echo $cake['product_id']; ?>"
                                                   name="tiers" 
                                                   class="form-control" 
                                                   min="1" 
                                                   max="<?php echo $cake['max_tiers']; ?>" 
                                                   required>
                                            <div class="invalid-feedback">Please select between 1 and <?php echo $cake['max_tiers']; ?> tiers.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="size<?php echo $cake['product_id']; ?>" class="form-label">Size (in inches)</label>
                                            <input type="number" 
                                                   id="size<?php echo $cake['product_id']; ?>"
                                                   name="size_in_inches" 
                                                   class="form-control" 
                                                   min="6" 
                                                   max="30" 
                                                   required>
                                            <div class="invalid-feedback">Please select a size between 6 and 30 inches.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="flavor<?php echo $cake['product_id']; ?>" class="form-label">Flavor</label>
                                            <select id="flavor<?php echo $cake['product_id']; ?>" 
                                                    name="flavor" 
                                                    class="form-control" 
                                                    required>
                                                <option value="">Select a flavor</option>
                                                <?php foreach ($flavors as $flavor): ?>
                                                    <option value="<?php echo htmlspecialchars(trim($flavor)); ?>">
                                                        <?php echo htmlspecialchars(ucfirst(trim($flavor))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a flavor.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="message<?php echo $cake['product_id']; ?>" class="form-label">
                                                Dedication Message (Max 7 words)
                                            </label>
                                            <input type="text" 
                                                   id="message<?php echo $cake['product_id']; ?>"
                                                   name="message" 
                                                   class="form-control" 
                                                   maxlength="35" 
                                                   required>
                                            <div class="invalid-feedback">Please enter a dedication message (max 7 words).</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="instructions<?php echo $cake['product_id']; ?>" class="form-label">
                                                Specific Instructions
                                            </label>
                                            <textarea id="instructions<?php echo $cake['product_id']; ?>"
                                                      name="specific_instructions" 
                                                      class="form-control" 
                                                      maxlength="500"></textarea>
                                            <div class="invalid-feedback">Instructions cannot exceed 500 characters.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="images<?php echo $cake['product_id']; ?>" class="form-label">
                                                Attach Reference Images (Max 3 files, 5MB each)
                                            </label>
                                            <input type="file" 
                                                   id="images<?php echo $cake['product_id']; ?>"
                                                   name="reference_images[]" 
                                                   class="form-control" 
                                                   multiple 
                                                   accept="image/*"
                                                   data-max-files="3"
                                                   data-max-size="5242880">
                                            <div class="invalid-feedback">Please select up to 3 images (5MB each).</div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No cakes available at the moment. Please check back later!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Your Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cartModalBody">
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <ul class="list-group">
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name'] ?? 'Unknown Product'); ?></strong><br>
                                    Quantity: <?php echo htmlspecialchars($item['quantity'] ?? 1); ?>
                                    <?php if (isset($item['customized']) && $item['customized']): ?>
                                        <span class="badge bg-success">Customized</span>
                                    <?php endif; ?>
                                    <br>
                                    Price: $<?php echo number_format($item['price'] ?? 0, 2); ?>
                                </div>
                                <button type="button" 
                                        class="btn btn-danger btn-sm remove-from-cart"
                                        data-index="<?php echo $index; ?>">
                                    Remove
                                </button>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                        <div class="mt-3">
                            <strong>Total: $<?php 
                                $total = 0;
                                foreach ($_SESSION['cart'] as $item) {
                                    $total += isset($item['price']) ? (float)$item['price'] : 0;
                                }
                                echo number_format($total, 2);
                            ?></strong>
                        </div>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Item added to cart successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Form validation
            const forms = document.querySelectorAll('.customize-form');
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    if (!form.checkValidity()) {
                        event.stopPropagation();
                        form.classList.add('was-validated');
                        return;
                    }

                    const formData = new FormData(form);
                    addToCart(formData);
                });
            });

            // Add to cart functionality
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', () => {
                    const productId = button.getAttribute('data-product-id');
                    const customized = button.getAttribute('data-customized');
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('customized', customized);
                    formData.append('quantity', 1);
                    
                    addToCart(formData);
                });
            });

            // Remove from cart functionality
            document.querySelectorAll('.remove-from-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    removeFromCart(index);
                });
            });

            // File upload validation
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function() {
                    const maxFiles = this.getAttribute('data-max-files');
                    const maxSize = this.getAttribute('data-max-size');
                    
                    if (this.files.length > maxFiles) {
                        this.value = '';
                        this.classList.add('is-invalid');
                        return;
                    }

                    for (let file of this.files) {
                        if (file.size > maxSize) {
                            this.value = '';
                            this.classList.add('is-invalid');
                            return;
                        }
                    }
                    
                    this.classList.remove('is-invalid');
                });
            });

            // Cart functions
            function addToCart(formData) {
                fetch('cart/add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Check if quantity is still available
                        if (data.quantity_available) {
                            // Show success toast
                            const toast = new bootstrap.Toast(document.getElementById('toast'));
                            toast.show();

                            // Update cart count badge
                            document.querySelector('.badge').textContent = data.cartCount;

                            // Update cart modal content
                            updateCartModal(data.cartItems);

                            // Close any open modals
                            const openModals = document.querySelectorAll('.modal.show');
                            openModals.forEach(modal => {
                                const modalInstance = bootstrap.Modal.getInstance(modal);
                                modalInstance.hide();
                            });
                        } else {
                            alert('Sorry, this item is no longer available in the requested quantity.');
                        }
                    } else {
                        alert(data.message || 'Failed to add item to cart.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart.');
                });
            }

            function removeFromCart(index) {
                fetch('cart/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ index: index })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.badge').textContent = data.cartCount;
                        updateCartModal(data.cartItems);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing from cart.');
                });
            }

            function updateCartModal(cartItems) {
                const cartModalBody = document.getElementById('cartModalBody');
                if (cartItems && cartItems.length > 0) {
                    let html = '<ul class="list-group">';
                    let total = 0;
                    
                    cartItems.forEach((item, index) => {
                        total += parseFloat(item.price);
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${item.product_name}</strong><br>
                                    Quantity: ${item.quantity}
                                    ${item.customized ? '<span class="badge bg-success">Customized</span>' : ''}
                                    <br>
                                    Price: $${parseFloat(item.price).toFixed(2)}
                                </div>
                                <button type="button" 
                                        class="btn btn-danger btn-sm remove-from-cart"
                                        data-index="${index}">
                                    Remove
                                </button>
                            </li>`;
                    });
                    
                    html += '</ul>';
                    html += `<div class="mt-3"><strong>Total: $${total.toFixed(2)}</strong></div>`;
                    cartModalBody.innerHTML = html;

                    // Reattach event listeners to new remove buttons
                    cartModalBody.querySelectorAll('.remove-from-cart').forEach(button => {
                        button.addEventListener('click', function() {
                            removeFromCart(this.getAttribute('data-index'));
                        });
                    });
                } else {
                    cartModalBody.innerHTML = '<p>Your cart is empty.</p>';
                }
            }
        });
    </script>
</body>
</html>