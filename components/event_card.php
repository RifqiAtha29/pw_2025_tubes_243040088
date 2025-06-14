<div class="event-card bg-white rounded-lg overflow-hidden shadow-lg">
    <img src="<?= $event['image_url'] ?? 'placeholder.jpg' ?>" class="w-full h-32 object-cover">
    <div class="p-4">
        <h4 class="font-bold"><?= htmlspecialchars($event['title']) ?></h4>
        <p class="text-gray-600 text-sm"><?= date('d M Y', strtotime($event['date'])) ?></p>
        <a href="event_detail.php?id=<?= $event['id'] ?>" class="inline-block mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm">Lihat</a>
    </div>
</div>
