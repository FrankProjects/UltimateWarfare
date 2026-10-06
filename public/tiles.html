<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hex Tile Demo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #1a1a2e; color: #eee; font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; }
        h1 { text-align: center; margin-bottom: 10px; font-size: 24px; color: #a0c4ff; }
        p.subtitle { text-align: center; margin-bottom: 40px; color: #888; font-size: 14px; }
        .tiles { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; max-width: 900px; margin: 0 auto; }
        .tile { text-align: center; }
        .tile canvas { display: block; margin: 0 auto 8px; }
        .tile .label { font-size: 13px; color: #ccc; text-transform: capitalize; }
        .tile .label span { display: block; font-size: 11px; color: #666; margin-top: 2px; }
        .map-demo { margin: 60px auto 0; text-align: center; }
        .map-demo h2 { font-size: 18px; color: #a0c4ff; margin-bottom: 15px; }
        #mapCanvas { border: 1px solid #333; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>Hex Terrain Tiles</h1>
    <p class="subtitle">Pointy-top hexagons &middot; hexSize 40 &middot; 8 terrain types</p>
    <div class="tiles" id="tiles"></div>

    <div class="map-demo">
        <h2>Sample Hex Map</h2>
        <canvas id="mapCanvas" width="640" height="420"></canvas>
    </div>

<script>
var TILES = [
    { name: 'deep_water',    file: 'images/map/deep_water.png',    desc: 'z < 130' },
    { name: 'water',         file: 'images/map/water.png',         desc: 'z < 150' },
    { name: 'shallow_water', file: 'images/map/shallow_water.png', desc: 'z < 160' },
    { name: 'sand',          file: 'images/map/sand.png',          desc: 'z < 168' },
    { name: 'grassland',     file: 'images/map/grassland.png',     desc: 'z < 200' },
    { name: 'forest',        file: 'images/map/forest.png',        desc: 'z < 230' },
    { name: 'hills',         file: 'images/map/hills.png',         desc: 'z < 250' },
    { name: 'mountain',      file: 'images/map/mountain.png',      desc: 'z >= 250' }
];

var HEX_SIZE = 40;
var HEX_W = Math.sqrt(3) * HEX_SIZE;
var HEX_H = 2 * HEX_SIZE;

function drawHex(ctx, cx, cy) {
    ctx.beginPath();
    for (var i = 0; i < 6; i++) {
        var a = (Math.PI / 180) * (60 * i - 30);
        var vx = cx + HEX_SIZE * Math.cos(a);
        var vy = cy + HEX_SIZE * Math.sin(a);
        if (i === 0) ctx.moveTo(vx, vy);
        else ctx.lineTo(vx, vy);
    }
    ctx.closePath();
}

function drawTileOnCanvas(canvas, img) {
    var ctx = canvas.getContext('2d');
    var cx = canvas.width / 2;
    var cy = canvas.height / 2;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Clip to hex and draw image
    ctx.save();
    drawHex(ctx, cx, cy);
    ctx.clip();
    ctx.drawImage(img, cx - HEX_W / 2, cy - HEX_H / 2, HEX_W, HEX_H);
    ctx.restore();

    // Border
    ctx.strokeStyle = 'rgba(255,255,255,0.25)';
    ctx.lineWidth = 1.5;
    drawHex(ctx, cx, cy);
    ctx.stroke();
}

// Load all tile images
var images = {};
var loaded = 0;

TILES.forEach(function(t) {
    var img = new Image();
    img.onload = function() {
        images[t.name] = img;
        loaded++;
        if (loaded === TILES.length) onAllLoaded();
    };
    img.src = t.file;
});

function onAllLoaded() {
    // Render individual tile previews
    var container = document.getElementById('tiles');
    TILES.forEach(function(t) {
        var div = document.createElement('div');
        div.className = 'tile';

        var canvas = document.createElement('canvas');
        canvas.width = Math.ceil(HEX_W) + 10;
        canvas.height = HEX_H + 10;
        drawTileOnCanvas(canvas, images[t.name]);

        var label = document.createElement('div');
        label.className = 'label';
        label.innerHTML = t.name.replace(/_/g, ' ') + '<span>' + t.desc + '</span>';

        div.appendChild(canvas);
        div.appendChild(label);
        container.appendChild(div);
    });

    // Render sample map
    renderSampleMap();
}

function renderSampleMap() {
    var canvas = document.getElementById('mapCanvas');
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = '#111';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Simple elevation map using a gradient island pattern
    var cols = 9, rows = 8;
    var offsetX = 50, offsetY = 30;
    var centerCol = cols / 2, centerRow = rows / 2;

    // Elevation thresholds matching the tile order
    var tileOrder = ['deep_water','water','shallow_water','sand','grassland','forest','hills','mountain'];

    for (var row = 0; row < rows; row++) {
        for (var col = 0; col < cols; col++) {
            // Hex center position (odd-r offset)
            var cx = offsetX + col * HEX_W + (row % 2) * (HEX_W / 2);
            var cy = offsetY + row * (HEX_H * 0.75);

            // Distance from center -> elevation
            var dx = (col - centerCol) / (cols / 2);
            var dy = (row - centerRow) / (rows / 2);
            var dist = Math.sqrt(dx * dx + dy * dy);

            // Add some pseudo-random variation
            var noise = Math.sin(col * 3.7 + row * 2.3) * 0.15
                      + Math.cos(col * 1.3 + row * 5.1) * 0.1;
            var elevation = 1 - dist + noise;

            // Map elevation to tile index
            var idx;
            if (elevation < 0.15) idx = 0;
            else if (elevation < 0.30) idx = 1;
            else if (elevation < 0.40) idx = 2;
            else if (elevation < 0.48) idx = 3;
            else if (elevation < 0.65) idx = 4;
            else if (elevation < 0.80) idx = 5;
            else if (elevation < 0.90) idx = 6;
            else idx = 7;

            var tileName = tileOrder[idx];
            var img = images[tileName];

            // Clip and draw
            ctx.save();
            drawHex(ctx, cx, cy);
            ctx.clip();
            ctx.drawImage(img, cx - HEX_W / 2, cy - HEX_H / 2, HEX_W, HEX_H);
            ctx.restore();

            // Subtle border
            ctx.strokeStyle = 'rgba(0,0,0,0.3)';
            ctx.lineWidth = 1;
            drawHex(ctx, cx, cy);
            ctx.stroke();
        }
    }
}
</script>
</body>
</html>
