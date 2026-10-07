/**
 * Simple Galeri Management JavaScript
 * For debugging purposes
 */

console.log('Galeri JavaScript loaded');

function testGaleri() {
    console.log('Testing galeri functionality...');
    
    fetch('api/test_galeri.php')
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            if (data.success) {
                renderGaleriGrid(data.data);
            }
        })
        .catch(error => {
            console.error('API Error:', error);
        });
}

function renderGaleriGrid(data) {
    const container = document.getElementById('galeriContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    data.forEach(galeri => {
        const galeriItem = document.createElement('div');
        galeriItem.className = 'col-md-6 col-lg-4 mb-4 galeri-item';
        galeriItem.innerHTML = `
            <div class="card h-100">
                <img src="${galeri.image_url}" class="galeri-image" alt="${galeri.title}">
                <div class="card-body">
                    <h6 class="card-title">${galeri.title}</h6>
                    <p class="card-text">${galeri.description || 'No description'}</p>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-primary" onclick="editGaleri(${galeri.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteGaleri(${galeri.id})">Delete</button>
                </div>
            </div>
        `;
        container.appendChild(galeriItem);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    testGaleri();
}); 