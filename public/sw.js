// Presensia PWA Service Worker
// Version: 1.0.0
// Last Updated: 2024

const CACHE_NAME = 'presensia-v1.0.1';
const STATIC_CACHE = 'presensia-static-v1.0.1';
const DYNAMIC_CACHE = 'presensia-dynamic-v1.0.1';

// Files to cache for offline use
const STATIC_FILES = [
    '/',
    '/login',
    '/attendance/check-in',
    '/attendance/check-out',
    '/attendance/reports',
    '/dashboard',
    '/manifest.json',
    '/icons/icon-16x16.png',
    '/icons/icon-32x32.png',
    '/icons/icon-72x72.png',
    '/icons/icon-96x96.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/favicon.ico'
];

// Install event - cache static files
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Service Worker: Caching static files');
                // Cache files individually to handle errors gracefully
                return Promise.allSettled(
                    STATIC_FILES.map(url => 
                        fetch(url)
                            .then(response => {
                                if (response.ok) {
                                    return cache.put(url, response);
                                }
                                throw new Error(`Failed to fetch ${url}: ${response.status}`);
                            })
                            .catch(error => {
                                console.warn(`Service Worker: Failed to cache ${url}:`, error.message);
                                return null; // Continue with other files
                            })
                    )
                );
            })
            .then(() => {
                console.log('Service Worker: Static files cached');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Service Worker: Error caching static files', error);
                // Still skip waiting even if caching fails
                return self.skipWaiting();
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }
    
    // Handle different types of requests
    if (request.destination === 'document') {
        // HTML pages - try network first, fallback to cache
        event.respondWith(
            fetch(request)
                .then(response => {
                    // If successful, cache the response
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(DYNAMIC_CACHE)
                            .then(cache => cache.put(request, responseClone));
                    }
                    return response;
                })
                .catch(() => {
                    // Fallback to cache
                    return caches.match(request)
                        .then(response => {
                            if (response) {
                                return response;
                            }
                            // If no cache, return offline page
                            return caches.match('/offline.html');
                        });
                })
        );
    } else if (request.destination === 'image' || 
               request.destination === 'style' || 
               request.destination === 'script' ||
               request.destination === 'font') {
        // Static assets - try cache first, fallback to network
        event.respondWith(
            caches.match(request)
                .then(response => {
                    if (response) {
                        return response;
                    }
                    return fetch(request)
                        .then(response => {
                            // Cache successful responses
                            if (response.status === 200) {
                                const responseClone = response.clone();
                                caches.open(DYNAMIC_CACHE)
                                    .then(cache => cache.put(request, responseClone));
                            }
                            return response;
                        });
                })
        );
    }
});

// Background sync for attendance data
self.addEventListener('sync', event => {
    if (event.tag === 'attendance-sync') {
        console.log('Service Worker: Background sync for attendance');
        event.waitUntil(syncAttendanceData());
    }
});

// Push notification handler
self.addEventListener('push', event => {
    console.log('Service Worker: Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'Notifikasi dari Presensia',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Buka Aplikasi',
                icon: '/icons/icon-96x96.png'
            },
            {
                action: 'close',
                title: 'Tutup',
                icon: '/icons/icon-96x96.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('Presensia', options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', event => {
    console.log('Service Worker: Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Sync attendance data when back online
async function syncAttendanceData() {
    try {
        // Get pending attendance data from IndexedDB
        const pendingData = await getPendingAttendanceData();
        
        if (pendingData && pendingData.length > 0) {
            console.log('Service Worker: Syncing pending attendance data');
            
            for (const data of pendingData) {
                try {
                    const response = await fetch('/api/attendance/sync', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': data.csrf_token
                        },
                        body: JSON.stringify(data)
                    });
                    
                    if (response.ok) {
                        console.log('Service Worker: Attendance data synced successfully');
                        // Remove from pending data
                        await removePendingAttendanceData(data.id);
                    }
                } catch (error) {
                    console.error('Service Worker: Error syncing attendance data', error);
                }
            }
        }
    } catch (error) {
        console.error('Service Worker: Error in sync function', error);
    }
}

// Helper functions for IndexedDB
async function getPendingAttendanceData() {
    // Implementation for getting pending data from IndexedDB
    return [];
}

async function removePendingAttendanceData(id) {
    // Implementation for removing synced data from IndexedDB
    console.log('Service Worker: Removing pending data', id);
}

// Message handler
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

console.log('Service Worker: Loaded successfully');

