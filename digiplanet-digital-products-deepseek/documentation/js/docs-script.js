/**
 * Digiplanet Digital Products Documentation Script
 * 
 * @package Digiplanet_Digital_Products
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Documentation namespace
    const DigiplanetDocs = {
        
        /**
         * Initialize documentation
         */
        init: function() {
            this.bindEvents();
            this.initCodeCopy();
            this.initScrollSpy();
            this.initSearch();
            this.updateLastModified();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Mobile menu toggle
            $('.digiplanet-docs-mobile-toggle').on('click', this.toggleMobileMenu);
            
            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', this.smoothScroll);
            
            // Table of contents toggle
            $('.digiplanet-docs-toc-toggle').on('click', this.toggleTableOfContents);
            
            // Theme toggle
            $('.digiplanet-docs-theme-toggle').on('click', this.toggleTheme);
            
            // Expand/collapse sections
            $('.digiplanet-docs-expand-toggle').on('click', this.toggleExpand);
        },
        
        /**
         * Initialize code copy functionality
         */
        initCodeCopy: function() {
            $('.digiplanet-docs-code-copy').on('click', function() {
                const $button = $(this);
                const $codeBlock = $button.closest('.digiplanet-docs-code-block');
                const code = $codeBlock.find('code').text();
                
                // Create temporary textarea
                const $textarea = $('<textarea>');
                $textarea.val(code).css({
                    position: 'absolute',
                    left: '-9999px'
                }).appendTo('body');
                
                // Select and copy
                $textarea.select();
                document.execCommand('copy');
                $textarea.remove();
                
                // Show feedback
                const originalText = $button.text();
                $button.text('Copied!');
                $button.css('background', '#27ae60');
                
                setTimeout(function() {
                    $button.text(originalText);
                    $button.css('background', '');
                }, 2000);
            });
        },
        
        /**
         * Initialize scroll spy for navigation
         */
        initScrollSpy: function() {
            const sections = $('.digiplanet-docs-section h2[id], .digiplanet-docs-section h3[id]');
            const navLinks = $('.digiplanet-docs-nav-link');
            
            $(window).on('scroll', function() {
                const scrollPos = $(window).scrollTop() + 100;
                let currentSection = '';
                
                sections.each(function() {
                    const sectionTop = $(this).offset().top;
                    const sectionHeight = $(this).outerHeight();
                    
                    if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                        currentSection = $(this).attr('id');
                        return false;
                    }
                });
                
                navLinks.removeClass('active');
                navLinks.filter('[href="#' + currentSection + '"]').addClass('active');
            });
        },
        
        /**
         * Initialize search functionality
         */
        initSearch: function() {
            const $searchInput = $('.digiplanet-docs-search-input');
            const $searchResults = $('.digiplanet-docs-search-results');
            
            if (!$searchInput.length) return;
            
            $searchInput.on('input', function() {
                const query = $(this).val().toLowerCase().trim();
                
                if (query.length < 2) {
                    $searchResults.hide().empty();
                    return;
                }
                
                // Search through documentation content
                const results = this.searchContent(query);
                this.displaySearchResults(results, query);
            }).on('keyup', function(e) {
                if (e.key === 'Escape') {
                    $searchResults.hide().empty();
                    $searchInput.val('');
                }
            });
            
            // Close search on click outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.digiplanet-docs-search').length) {
                    $searchResults.hide().empty();
                }
            });
        },
        
        /**
         * Search documentation content
         */
        searchContent: function(query) {
            const results = [];
            const $sections = $('.digiplanet-docs-section');
            
            $sections.each(function() {
                const $section = $(this);
                const title = $section.find('h2').text();
                const content = $section.text().toLowerCase();
                
                if (content.includes(query)) {
                    const headings = [];
                    $section.find('h3').each(function() {
                        headings.push($(this).text());
                    });
                    
                    // Find matching text with context
                    const regex = new RegExp(`.{0,100}${query}.{0,100}`, 'gi');
                    const matches = content.match(regex) || [];
                    
                    results.push({
                        title: title,
                        headings: headings,
                        matches: matches.slice(0, 3), // Limit to 3 matches
                        url: window.location.pathname + '#' + $section.find('h2').attr('id')
                    });
                }
            });
            
            return results;
        },
        
        /**
         * Display search results
         */
        displaySearchResults: function(results, query) {
            const $searchResults = $('.digiplanet-docs-search-results');
            
            if (results.length === 0) {
                $searchResults.html('<div class="digiplanet-docs-no-results">No results found for "' + query + '"</div>').show();
                return;
            }
            
            let html = '<div class="digiplanet-docs-search-results-list">';
            
            results.forEach(function(result) {
                html += '<div class="digiplanet-docs-search-result">';
                html += '<h4><a href="' + result.url + '">' + result.title + '</a></h4>';
                
                if (result.headings.length > 0) {
                    html += '<div class="digiplanet-docs-search-headings">';
                    result.headings.forEach(function(heading) {
                        html += '<span>' + heading + '</span>';
                    });
                    html += '</div>';
                }
                
                if (result.matches.length > 0) {
                    html += '<div class="digiplanet-docs-search-snippets">';
                    result.matches.forEach(function(match) {
                        const highlighted = match.replace(
                            new RegExp(query, 'gi'),
                            '<mark>$&</mark>'
                        );
                        html += '<p>' + highlighted + '...</p>';
                    });
                    html += '</div>';
                }
                
                html += '</div>';
            });
            
            html += '</div>';
            $searchResults.html(html).show();
        },
        
        /**
         * Toggle mobile menu
         */
        toggleMobileMenu: function() {
            $('.digiplanet-docs-sidebar').toggleClass('digiplanet-docs-sidebar-open');
            $('body').toggleClass('digiplanet-docs-no-scroll');
        },
        
        /**
         * Smooth scroll to anchor
         */
        smoothScroll: function(e) {
            e.preventDefault();
            
            const target = $(this).attr('href');
            if (target === '#') return;
            
            const $target = $(target);
            if ($target.length) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - 80
                }, 500);
                
                // Update URL without page reload
                if (history.pushState) {
                    history.pushState(null, null, target);
                }
                
                // Close mobile menu if open
                $('.digiplanet-docs-sidebar').removeClass('digiplanet-docs-sidebar-open');
                $('body').removeClass('digiplanet-docs-no-scroll');
            }
        },
        
        /**
         * Toggle table of contents
         */
        toggleTableOfContents: function() {
            $('.digiplanet-docs-toc').toggleClass('digiplanet-docs-toc-open');
        },
        
        /**
         * Toggle theme (light/dark)
         */
        toggleTheme: function() {
            $('body').toggleClass('digiplanet-docs-dark');
            
            const isDark = $('body').hasClass('digiplanet-docs-dark');
            localStorage.setItem('digiplanet-docs-theme', isDark ? 'dark' : 'light');
            
            $(this).find('i').toggleClass('dashicons-lightbulb dashicons-marker');
        },
        
        /**
         * Toggle expand/collapse section
         */
        toggleExpand: function() {
            const $button = $(this);
            const $section = $button.closest('.digiplanet-docs-section');
            const $content = $section.find('.digiplanet-docs-section-content');
            
            $content.slideToggle(300, function() {
                $button.toggleClass('digiplanet-docs-expanded');
                $section.toggleClass('digiplanet-docs-collapsed');
            });
        },
        
        /**
         * Update last modified date
         */
        updateLastModified: function() {
            const $lastModified = $('.digiplanet-docs-last-modified');
            if ($lastModified.length) {
                const lastModified = new Date(document.lastModified);
                const options = { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                $lastModified.text(lastModified.toLocaleDateString('en-US', options));
            }
        },
        
        /**
         * Initialize syntax highlighting
         */
        initSyntaxHighlighting: function() {
            // Check if Prism.js is loaded
            if (typeof Prism !== 'undefined') {
                Prism.highlightAll();
            } else {
                // Fallback basic highlighting
                this.initBasicHighlighting();
            }
        },
        
        /**
         * Basic syntax highlighting fallback
         */
        initBasicHighlighting: function() {
            $('pre code').each(function() {
                const code = $(this).html();
                const highlighted = code
                    .replace(/&lt;\?php/g, '<span class="digiplanet-php-tag">&lt;?php</span>')
                    .replace(/&lt;\/?php/g, '<span class="digiplanet-php-tag">$&</span>')
                    .replace(/\b(function|class|if|else|foreach|return|echo|print|new|array)\b/g, '<span class="digiplanet-keyword">$1</span>')
                    .replace(/\/\/.*$/gm, '<span class="digiplanet-comment">$&</span>')
                    .replace(/(".*?"|'.*?')/g, '<span class="digiplanet-string">$1</span>')
                    .replace(/\b(\d+)\b/g, '<span class="digiplanet-number">$1</span>')
                    .replace(/(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/g, '<span class="digiplanet-variable">$1</span>');
                
                $(this).html(highlighted);
            });
        },
        
        /**
         * Initialize responsive tables
         */
        initResponsiveTables: function() {
            $('.digiplanet-docs-table').each(function() {
                const $table = $(this);
                if ($table.width() > $(window).width()) {
                    $table.wrap('<div class="digiplanet-docs-table-responsive"></div>');
                }
            });
        },
        
        /**
         * Print documentation
         */
        printDocumentation: function() {
            window.print();
        },
        
        /**
         * Generate table of contents
         */
        generateTableOfContents: function() {
            const $toc = $('.digiplanet-docs-toc-list');
            if (!$toc.length) return;
            
            const headings = $('.digiplanet-docs-section h2[id], .digiplanet-docs-section h3[id]');
            let html = '';
            let currentLevel = 0;
            
            headings.each(function() {
                const $heading = $(this);
                const level = $heading.prop('tagName') === 'H2' ? 1 : 2;
                const id = $heading.attr('id');
                const text = $heading.text();
                
                if (level === 1) {
                    if (currentLevel === 2) {
                        html += '</ul>';
                    }
                    html += '<li><a href="#' + id + '">' + text + '</a>';
                    currentLevel = 1;
                } else if (level === 2) {
                    if (currentLevel === 1) {
                        html += '<ul>';
                    } else if (currentLevel === 0) {
                        html += '<li><ul>';
                    }
                    html += '<li><a href="#' + id + '">' + text + '</a></li>';
                    currentLevel = 2;
                }
            });
            
            if (currentLevel === 2) {
                html += '</ul>';
            }
            
            if (currentLevel > 0) {
                html += '</li>';
            }
            
            $toc.html(html);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        DigiplanetDocs.init();
        DigiplanetDocs.initSyntaxHighlighting();
        DigiplanetDocs.initResponsiveTables();
        DigiplanetDocs.generateTableOfContents();
        
        // Apply saved theme preference
        const savedTheme = localStorage.getItem('digiplanet-docs-theme');
        if (savedTheme === 'dark') {
            $('body').addClass('digiplanet-docs-dark');
            $('.digiplanet-docs-theme-toggle i').toggleClass('dashicons-lightbulb dashicons-marker');
        }
    });
    
})(jQuery);