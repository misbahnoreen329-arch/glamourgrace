// Main JavaScript file for GlamorousGrace

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all sliders
    initSliders();
    
    // Initialize cart functionality
    initCart();
    
    // Initialize image galleries
    initImageGalleries();
});

function initSliders() {
    // Banner slider
    const bannerSlider = document.querySelector('.banner-slider');
    if (bannerSlider) {
        const slides = bannerSlider.querySelectorAll('.slide');
        const prevBtn = bannerSlider.querySelector('.slider-btn.prev');
        const nextBtn = bannerSlider.querySelector('.slider-btn.next');
        
        let currentSlide = 0;
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.display = i === index ? 'block' : 'none';
            });
            currentSlide = index;
        }
        
        function nextSlide() {
            let next = currentSlide + 1;
            if (next >= slides.length) next = 0;
            showSlide(next);
        }
        
        function prevSlide() {
            let prev = currentSlide - 1;
            if (prev < 0) prev = slides.length - 1;
            showSlide(prev);
        }
        
        // Auto slide every 5 seconds
        let slideInterval = setInterval(nextSlide, 5000);
        
        // Pause on hover
        bannerSlider.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        bannerSlider.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 5000);
        });
        
        // Button controls
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        
        // Show first slide
        showSlide(0);
    }
    
    // Product sliders
    document.querySelectorAll('.product-slider').forEach(slider => {
        const container = slider.querySelector('.products-grid');
        const products = container.querySelectorAll('.product-card');
        const prevBtn = slider.querySelector('.slider-nav.prev');
        const nextBtn = slider.querySelector('.slider-nav.next');
        
        if (products.length > 4 && prevBtn && nextBtn) {
            let currentIndex = 0;
            const productsPerView = window.innerWidth < 768 ? 1 : 
                                  window.innerWidth < 1024 ? 2 : 4;
            
            function updateSlider() {
                products.forEach((product, index) => {
                    product.style.display = (index >= currentIndex && index < currentIndex + productsPerView) ? 
                                          'block' : 'none';
                });
            }
            
            prevBtn.addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateSlider();
                }
            });
            
            nextBtn.addEventListener('click', () => {
                if (currentIndex + productsPerView < products.length) {
                    currentIndex++;
                    updateSlider();
                }
            });
            
            updateSlider();
            
            // Auto slide for product sliders
            setInterval(() => {
                if (currentIndex + productsPerView < products.length) {
                    currentIndex++;
                    updateSlider();
                } else {
                    currentIndex = 0;
                    updateSlider();
                }
            }, 5000);
        }
    });
}

function initCart() {
    // Add to cart buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.add-to-cart') || e.target.closest('.add-to-cart')) {
            e.preventDefault();
            
            const button = e.target.matches('.add-to-cart') ? e.target : e.target.closest('.add-to-cart');
            const productId = button.getAttribute('data-id');
            const quantity = button.getAttribute('data-quantity') || 1;
            
            addToCart(productId, quantity);
        }
    });
}

function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(count => {
        showNotification('Product added to cart!');
        updateCartCount(count);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}

function updateCartCount(count) {
    let cartCount = document.querySelector('.cart-count');
    
    if (!cartCount) {
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.innerHTML = '🛒 <span class="cart-count">' + count + '</span>';
            cartCount = cartIcon.querySelector('.cart-count');
        }
    } else {
        cartCount.textContent = count;
    }
    
    // Show/hide cart count
    if (count > 0) {
        cartCount.style.display = 'inline-block';
    } else {
        cartCount.style.display = 'none';
    }
}

function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        border-radius: 5px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function initImageGalleries() {
    // Product image gallery
    document.querySelectorAll('.thumbnail-images img').forEach(thumb => {
        thumb.addEventListener('click', function() {
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                mainImage.src = this.src.replace('/thumbnails/', '/');
            }
            
            // Update active state
            this.parentNode.querySelectorAll('img').forEach(img => {
                img.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification {
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }
`;
document.head.appendChild(style);