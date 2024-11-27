<?php
// Récupération des articles de la 6ᵉ à la 10ᵉ position
$stmt = $pdo->prepare("
    SELECT id, titre, date_publication, image_upload
    FROM articles
    WHERE status = 'publié'
    ORDER BY date_publication DESC
    LIMIT 5 OFFSET 5
");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($articles): ?>
    <div>
        <h4 class="fst-italic">Recent posts</h4>
        <ul class="list-unstyled">
            <?php foreach ($articles as $article): ?>
                <li>
                    <div class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top">
                        <!-- Image de l'article ou SVG par défaut -->
                        <?php if ($article['image_upload']): ?>
                            <img src="<?php echo htmlspecialchars($article['image_upload']); ?>" class="bd-placeholder-img" width="100%" height="96" alt="Image de l'article"/>
                        <?php else: ?>
                            <!-- SVG miniature si l'image n'est pas disponible -->
                            <svg class="bd-placeholder-img" width="100%" height="96" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false">
                                <rect width="100%" height="100%" fill="#777"/>
                            </svg>
                        <?php endif; ?>

                        <!-- Titre et date -->
                        <div class="col-lg-8">
                            <h6 class="mb-0"><?php echo htmlspecialchars($article['titre']); ?></h6>
                            <small class="text-body-secondary">
                                <?php echo date('F d, Y', strtotime($article['date_publication'])); ?>
                            </small>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
