<style id="cash-game-full-width-styles">
    /* This style block will be enabled/disabled by JavaScript based on URL */
</style>

<script>
(function() {
    // Check if we're on a cash game page
    function isCashGamePage() {
        const path = window.location.pathname;
        return path.includes('/cash-games') && (path.includes('/create') || path.includes('/edit'));
    }
    
    // Inject CSS dynamically
    function injectCSS() {
        const styleId = 'cash-game-dynamic-styles';
        let style = document.getElementById(styleId);
        
        if (!style) {
            style = document.createElement('style');
            style.id = styleId;
            document.head.appendChild(style);
        }
        
        if (isCashGamePage()) {
            style.textContent = `
                /* Force full width for cash game pages */
                .fi-page {
                    max-width: 100% !important;
                }
                
                /* Force single column on ALL grids */
                .fi-page [class*="grid"],
                .fi-page [class*="grid-cols"] {
                    grid-template-columns: 1fr !important;
                }
                
                /* Hide second column */
                .fi-page > div > div:nth-child(2),
                .fi-page > div[class*="grid"] > div:nth-child(2),
                .fi-page > div[class*="flex"] > div:nth-child(2) {
                    display: none !important;
                }
                
                /* Make first column full width */
                .fi-page > div > div:first-child,
                .fi-page > div[class*="grid"] > div:first-child,
                .fi-page > div[class*="flex"] > div:first-child {
                    max-width: 100% !important;
                    width: 100% !important;
                    flex: 1 1 100% !important;
                    grid-column: 1 / -1 !important;
                }
                
                /* Main containers */
                .fi-main-ctn,
                .fi-body,
                .fi-body > .fi-container,
                .fi-form,
                .fi-section-content-ctn {
                    max-width: 100% !important;
                    width: 100% !important;
                }
                
                /* Tabs */
                .fi-tabs {
                    max-width: 100% !important;
                    width: 100% !important;
                }
            `;
        } else {
            style.textContent = '';
        }
    }
    
    // Apply full width with JavaScript DOM manipulation
    function applyFullWidth() {
        if (!isCashGamePage()) {
            injectCSS();
            return;
        }
        
        injectCSS();
        
        function forceFullWidth() {
            const page = document.querySelector('.fi-page');
            if (!page) return;
            
            // Find direct child divs (the two-column layout)
            const pageContainer = page.querySelector('> div');
            if (!pageContainer) return;
            
            const children = Array.from(pageContainer.children);
            
            // Hide second column and expand first
            children.forEach((child, index) => {
                if (index === 1) {
                    child.style.display = 'none';
                } else if (index === 0) {
                    child.style.maxWidth = '100%';
                    child.style.width = '100%';
                    child.style.flex = '1 1 100%';
                    child.style.gridColumn = '1 / -1';
                }
            });
            
            // Force all grids to single column
            const grids = page.querySelectorAll('[class*="grid"]');
            grids.forEach(grid => {
                const computedStyle = window.getComputedStyle(grid);
                if (computedStyle.display === 'grid') {
                    grid.style.gridTemplateColumns = '1fr';
                }
            });
        }
        
        // Run immediately
        forceFullWidth();
        
        // Run after a short delay to catch dynamic content
        setTimeout(forceFullWidth, 50);
        setTimeout(forceFullWidth, 200);
        setTimeout(forceFullWidth, 500);
        
        // Observe DOM changes
        const observer = new MutationObserver(function() {
            forceFullWidth();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }
    
    // Run on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyFullWidth);
    } else {
        applyFullWidth();
    }
    
    // Run on navigation
    let lastUrl = location.href;
    const navObserver = new MutationObserver(() => {
        const url = location.href;
        if (url !== lastUrl) {
            lastUrl = url;
            setTimeout(applyFullWidth, 100);
        }
    });
    navObserver.observe(document, { subtree: true, childList: true });
})();
</script>

