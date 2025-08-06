// Simple Service Worker for caching static assets
const CACHE_NAME = 'abedeport-v1';
const urlsToCache = [
  './',
  './index.html',
  './assets/landing/css/vendors.min.css',
  './assets/landing/css/style.css',
  './assets/landing/js/jquery.js',
  './assets/landing/js/vendors.min.js',
  './assets/landing/js/main.js',
  './assets/img/main_logo.png'
];

self.addEventListener('install', function(event) {
  console.log('Service Worker installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Caching files...');
        return cache.addAll(urlsToCache);
      })
      .catch(function(error) {
        console.log('Cache installation failed:', error);
      })
  );
});

self.addEventListener('activate', function(event) {
  console.log('Service Worker activated');
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', function(event) {
  // Only cache GET requests
  if (event.request.method === 'GET') {
    event.respondWith(
      caches.match(event.request)
        .then(function(response) {
          // Return cached version or fetch from network
          if (response) {
            console.log('Serving from cache:', event.request.url);
            return response;
          }
          return fetch(event.request);
        })
        .catch(function(error) {
          console.log('Fetch failed:', error);
        })
    );
  }
});