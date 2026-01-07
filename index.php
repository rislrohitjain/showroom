<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronics Showroom | Premium Electronics</title>
    
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/3659/3659899.png">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root {
            --amazon-blue: #131921;
            --amazon-light-blue: #232f3e;
            --amazon-orange: #febd69;
            --amazon-orange-hover: #f3a847;
            --bg-gray: #eaeded;
            --text-main: #0f1111;
        }

        /* ... Your existing styles remain same until Modal Styles below ... */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Public Sans', sans-serif; }
        body { background-color: var(--bg-gray); color: var(--text-main); line-height: 1.5; }
        header { background-color: var(--amazon-blue); color: white; position: sticky; top: 0; z-index: 1000; }
        .nav-top { display: flex; align-items: center; padding: 10px 20px; gap: 20px; }
        .logo { font-size: 24px; font-weight: 700; color: white; text-decoration: none; border: 1px solid transparent; padding: 5px; }
        .logo span { color: var(--amazon-orange); }
        .search-container { flex-grow: 1; display: flex; }
        .search-container input { width: 100%; padding: 10px; border: none; border-radius: 4px 0 0 4px; outline: none; }
        .search-btn { background: var(--amazon-orange); border: none; padding: 0 15px; border-radius: 0 4px 4px 0; cursor: pointer; font-size: 18px; }
        .hero { height: 400px; background: linear-gradient(to bottom, rgba(0,0,0,0.3), var(--bg-gray)), url('https://images.unsplash.com/photo-1593305841991-05c297ba4575?auto=format&fit=crop&q=80&w=1920'); background-size: cover; background-position: center; }
        .container { max-width: 1500px; margin: -150px auto 50px; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product-card { background: white; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; transition: transform 0.2s; }
        .product-card img { width: 100%; height: 200px; object-fit: contain; margin-bottom: 15px; }
        .price { font-size: 22px; font-weight: 700; color: #B12704; margin-bottom: 10px; }
        .add-btn { background: #ffd814; border: 1px solid #fcd200; border-radius: 20px; padding: 8px; cursor: pointer; font-weight: 600; }
        
        .support-float {
            position: fixed; bottom: 30px; right: 30px;
            background: var(--amazon-light-blue); color: white;
            padding: 15px 25px; border-radius: 50px;
            cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex; align-items: center; gap: 10px; z-index: 999;
        }

        /* ==========================================
           NEW OVERLAY MODAL & SPINNER STYLES
        ========================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
        }

        .modal-window {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 450px; height: 600px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }

        .modal-header {
            background: #f0f2f2;
            padding: 12px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 { font-size: 14px; font-weight: 700; }
        
        .close-modal {
            background: none; border: none; font-size: 22px; 
            cursor: pointer; color: #555;
        }

        .modal-body {
            flex-grow: 1;
            position: relative;
            background: #fff;
        }

        #supportFrame { width: 100%; height: 100%; border: none; display: none; }

        /* Amazon-style Spinner */
        .amazon-loader {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .spinner {
            width: 40px; height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--amazon-orange);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text { margin-top: 10px; font-size: 13px; color: #555; }
    </style>
	
	 <style>
	
	/* Footer Styles */
footer {
    background-color: var(--amazon-light-blue);
    color: white;
    margin-top: 50px;
}

.back-to-top {
    background-color: #37475a;
    text-align: center;
    padding: 15px 0;
    cursor: pointer;
    font-size: 13px;
    transition: background-color 0.2s;
}

.back-to-top:hover {
    background-color: #485769;
}

.footer-main {
    max-width: 1000px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    padding: 40px 20px;
    gap: 20px;
}

.footer-col h3 {
    font-size: 16px;
    margin-bottom: 15px;
    font-weight: 700;
}

.footer-col ul {
    list-style: none;
}

.footer-col ul li {
    margin-bottom: 10px;
}

.footer-col ul li a {
    color: #DDD;
    text-decoration: none;
    font-size: 14px;
}

.footer-col ul li a:hover {
    text-decoration: underline;
}

.footer-line {
    border-top: 1px solid #3a4553;
    padding: 30px 0;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 50px;
}

.footer-logo {
    font-size: 20px;
    font-weight: 700;
    color: white;
    text-decoration: none;
}

.footer-logo span { color: var(--amazon-orange); }

.lang-select {
    border: 1px solid #848688;
    padding: 6px 10px;
    border-radius: 3px;
    font-size: 12px;
    color: #CCC;
    cursor: pointer;
}

.footer-bottom {
    background-color: var(--amazon-blue);
    padding: 30px 0;
    text-align: center;
}

.footer-bottom ul {
    display: flex;
    justify-content: center;
    gap: 20px;
    list-style: none;
    margin-bottom: 10px;
}

.footer-bottom ul li a {
    font-size: 12px;
    color: white;
    text-decoration: none;
}

.footer-bottom p {
    font-size: 12px;
    color: #DDD;
}

@media (max-width: 768px) {
    .footer-main {
        grid-template-columns: repeat(2, 1fr);
    }
    .footer-line {
        flex-direction: column;
        gap: 20px;
    }
}
</style>

</head>
<body>

<header>
    <div class="nav-top">
        <a href="#" class="logo">Electra<span>Store</span></a>
        <div class="search-container">
            <input type="text" placeholder="Search Electronics...">
            <button class="search-btn"><i class="fas fa-search"></i></button>
        </div>
        <div class="nav-tools">
            <div class="nav-item"><span>Sign in</span></div>
            <div class="nav-item"><i class="fas fa-shopping-cart"></i> <span>Cart</span></div>
        </div>
    </div>
</header>

<div class="hero"></div>

<div class="container">
    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71d7rfSl0wL._AC_SL1500_.jpg" alt="iPhone 15">
        <h3>Apple iPhone 15 Pro Max (256 GB)</h3>
        <div class="price">₹1,48,900</div>
        <button class="add-btn">Add to Cart</button>
    </div>
	
	
    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71I4NoKj-oL._SX679_.jpg" alt="Laptop">
        <h3>Dell XPS 13 Laptop - Intel Core i7, 16GB RAM, 512GB SSD</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> <span>(850)</span></div>
        <div class="price">₹1,24,000</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71L2iBSyyOL._AC_SL1500_.jpg" alt="Sony TV">
        <h3>Sony Bravia 65-inch 4K Ultra HD Smart LED Google TV</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(2,100)</span></div>
        <div class="price">₹85,990</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/61AHiYyu3ZL._AC_SL1500_.jpg" alt="Smartwatch">
        <h3>Samsung Galaxy Watch 6 Bluetooth (44mm, Graphite)</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> <span>(4,560)</span></div>
        <div class="price">₹28,500</div>
        <button class="add-btn">Add to Cart</button>
    </div>
	
	
	 
    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71d7rfSl0wL._AC_SL1500_.jpg" alt="iPhone 15">
        <h3>Apple iPhone 15 Pro Max (256 GB) - Blue Titanium</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i> <span>(14,203)</span></div>
        <div class="price">₹1,48,900</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/717Q2swzhBL._SL1500_.jpg" alt="Samsung S24">
        <h3>Samsung Galaxy S24 Ultra 5G (Titanium Gray, 12GB RAM, 256GB Storage)</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(8,120)</span></div>
        <div class="price">₹1,29,999</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71r0349s3cL._SL1500_.jpg" alt="Google Pixel 8">
        <h3>Google Pixel 8 Pro (Obsidian, 128GB)</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> <span>(1,450)</span></div>
        <div class="price">₹1,06,999</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71I4NoKj-oL._SX679_.jpg" alt="Laptop">
        <h3>Dell XPS 13 Laptop - Intel Core i7, 16GB RAM, 512GB SSD</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> <span>(850)</span></div>
        <div class="price">₹1,24,000</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71f5Eu5lJSL._SX679_.jpg" alt="MacBook Air">
        <h3>Apple MacBook Air Laptop M2 chip: 13.6-inch Liquid Retina Display</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(5,200)</span></div>
        <div class="price">₹99,900</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71vFKBpKakL._SX679_.jpg" alt="ASUS ROG">
        <h3>ASUS ROG Zephyrus G14, 14" QHD 120Hz, Ryzen 9, RTX 3060</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i> <span>(430)</span></div>
        <div class="price">₹1,45,990</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/71L2iBSyyOL._AC_SL1500_.jpg" alt="Sony TV">
        <h3>Sony Bravia 65-inch 4K Ultra HD Smart LED Google TV</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(2,100)</span></div>
        <div class="price">₹85,990</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://m.media-amazon.com/images/I/81KymXHO53L._SL1500_.jpg" alt="LG OLED">
        <h3>LG 55 inches 4K Ultra HD Smart OLED TV</h3>
        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(1,200)</span></div>
        <div class="price">₹1,14,990</div>
        <button class="add-btn">Add to Cart</button>
    </div>
 
	
</div>

<footer>
    <div class="back-to-top" id="backToTop">
        Back to top
    </div>

    <div class="footer-main">
        <div class="footer-col">
            <h3>Get to Know Us</h3>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Press Releases</a></li>
                <li><a href="#">Electra Science</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Connect with Us</h3>
            <ul>
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Twitter</a></li>
                <li><a href="#">Instagram</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Make Money with Us</h3>
            <ul>
                <li><a href="#">Sell on Electra</a></li>
                <li><a href="#">Supply to Electra</a></li>
                <li><a href="#">Become an Affiliate</a></li>
                <li><a href="#">Protect & Build Your Brand</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Let Us Help You</h3>
            <ul>
                <li><a href="#">Your Account</a></li>
                <li><a href="#">Returns Centre</a></li>
                <li><a href="#">100% Purchase Protection</a></li>
                <li><a href="#">Help</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-line">
        <a href="#" class="footer-logo">Electra<span>Store</span></a>
        <div class="lang-select">
            <i class="fas fa-globe"></i> English
        </div>
    </div>

    <div class="footer-bottom">
        <ul>
            <li><a href="#">Conditions of Use</a></li>
            <li><a href="#">Privacy Notice</a></li>
            <li><a href="#">Interest-Based Ads</a></li>
        </ul>
        <p>© 2024-2026, ElectraStore.com, Inc. or its affiliates</p>
    </div>
</footer>

<div class="support-float" id="supportBtn">
    <i class="fas fa-headset"></i>
    <span>Support Chat</span>
</div>

<div class="modal-overlay" id="modalOverlay">
    <div class="modal-window">
        <div class="modal-header">
            <h2>Customer Support</h2>
            <button class="close-modal" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="amazon-loader" id="amazonLoader">
                <div class="spinner"></div>
                <div class="loading-text">Loading Support...</div>
            </div>
            <iframe id="supportFrame" src=""></iframe>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const $overlay = $('#modalOverlay');
    const $frame = $('#supportFrame');
    const $loader = $('#amazonLoader');

    // OPEN MODAL
    $('#supportBtn').on('click', function() {
        $overlay.fadeIn(300);
        $loader.show();
        $frame.hide();

        // Load the page into iframe
        $frame.attr('src', 'front_view.php');
    });

    // CLOSE MODAL
    $('#closeModal, #modalOverlay').on('click', function(e) {
        if (e.target !== this) return; // Prevent closing if clicking inside window
        $overlay.fadeOut(200);
        $frame.attr('src', ''); // Clear iframe to save resources
    });

    // HIDE LOADER WHEN IFRAME FINISHES LOADING
    $frame.on('load', function() {
        $loader.hide();
        $frame.fadeIn(400);
    });

    // Back to top scroll
    $('#backToTop').click(function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>

</body>
</html>