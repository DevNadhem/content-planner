<?php
$jsonFile = 'posts.json';

// Initialize JSON file if missing
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([]));
}

function getPosts($file) {
    return json_decode(file_get_contents($file), true) ?? [];
}

function savePosts($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posts = getPosts($jsonFile);
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $newPost = [
            'id' => uniqid(),
            'title' => trim($_POST['title']),
            'platform' => $_POST['platform'],
            'scheduled_at' => $_POST['scheduled_at'],
            'status' => $_POST['status'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        array_unshift($posts, $newPost);
        savePosts($jsonFile, $posts);
    } elseif ($action === 'delete') {
        $idToDelete = $_POST['id'];
        $posts = array_filter($posts, fn($post) => $post['id'] !== $idToDelete);
        savePosts($jsonFile, array_values($posts));
    } elseif ($action === 'update_status') {
        $idToUpdate = $_POST['id'];
        $newStatus = $_POST['status'];
        foreach ($posts as &$post) {
            if ($post['id'] === $idToUpdate) {
                $post['status'] = $newStatus;
                break;
            }
        }
        savePosts($jsonFile, $posts);
    }

    header('Location: index.php');
    exit;
}

$posts = getPosts($jsonFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Planner Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6 font-sans">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-5">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-white">Content Planner Workspace</h1>
                <p class="text-slate-400 text-sm mt-1">Manage, schedule, and track content publishing pipelines.</p>
            </div>
            <div class="bg-indigo-600/20 text-indigo-400 px-4 py-2 rounded-lg border border-indigo-500/30 font-medium text-sm">
                <i class="fa-solid fa-database mr-2"></i>JSON Storage Active
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Create Post Panel -->
            <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-6 shadow-xl h-fit">
                <h2 class="text-lg font-semibold mb-4 flex items-center text-indigo-400">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Schedule New Post
                </h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Post Title</label>
                        <input type="text" name="title" required placeholder="e.g., Tech Stack Update Reel" 
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Platform</label>
                        <select name="platform" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 transition">
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Twitter/X">Twitter/X</option>
                            <option value="Instagram">Instagram</option>
                            <option value="YouTube">YouTube</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Scheduled Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" required 
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 transition text-slate-300">
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Status</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 transition">
                            <option value="Draft">Draft</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Published">Published</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2 px-4 rounded-lg transition shadow-lg shadow-indigo-600/30">
                        Add Content Item
                    </button>
                </form>
            </div>

            <!-- Content Pipeline Stream -->
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-lg font-semibold flex items-center text-slate-200">
                    <i class="fa-solid fa-list-check mr-2 text-indigo-400"></i> Scheduled Queue (<?= count($posts) ?>)
                </h2>

                <?php if (empty($posts)): ?>
                    <div class="bg-slate-800/30 border border-slate-800 rounded-xl p-8 text-center text-slate-500">
                        No content planned yet. Add your first scheduled post using the form.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($posts as $post): ?>
                            <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:border-slate-600 transition">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-800/50">
                                            <?= htmlspecialchars($post['platform']) ?>
                                        </span>
                                        <span class="text-xs px-2 py-0.5 rounded font-medium 
                                            <?= $post['status'] === 'Published' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/50' : '' ?>
                                            <?= $post['status'] === 'Scheduled' ? 'bg-amber-950 text-amber-400 border border-amber-800/50' : '' ?>
                                            <?= $post['status'] === 'Draft' ? 'bg-slate-700 text-slate-300 border border-slate-600' : '' ?>">
                                            <?= htmlspecialchars($post['status']) ?>
                                        </span>
                                    </div>
                                    <h3 class="text-base font-semibold text-white"><?= htmlspecialchars($post['title']) ?></h3>
                                    <p class="text-xs text-slate-400">
                                        <i class="fa-regular fa-clock mr-1"></i> Scheduled for: <?= htmlspecialchars($post['scheduled_at']) ?>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto justify-end border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-700">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 text-xs rounded px-2 py-1 text-slate-300 focus:outline-none">
                                            <option value="Draft" <?= $post['status'] === 'Draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="Scheduled" <?= $post['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                            <option value="Published" <?= $post['status'] === 'Published' ? 'selected' : '' ?>>Published</option>
                                        </select>
                                    </form>

                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                        <button type="submit" class="text-slate-400 hover:text-rose-400 p-1 transition" title="Delete Post">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>