<?php
$query = "
SELECT title, option_name, option_value, display_name, icon_class 
FROM options 
WHERE section = 'footer' 
ORDER BY 
    CASE title
        WHEN 'Liên kết' THEN 1
        WHEN 'Liên kết nhanh' THEN 2
        WHEN 'Liên hệ' THEN 3
        WHEN 'Theo dõi' THEN 4
        ELSE 5
    END ASC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$footer_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng để nhóm các tùy chọn theo title (từng cột box)
$footer_columns = [];

foreach ($footer_options as $option) {
    $footer_columns[$option['title']][] = $option; // Nhóm theo title
}
?>

<footer class="footer">
    <section class="grid">
        <?php foreach ($footer_columns as $title => $options): ?>
        <div class="box">
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <?php foreach ($options as $option): ?>
                <a href="<?php echo htmlspecialchars($option['option_value']); ?>">
                    <?php if (!empty($option['icon_class'])): ?>
                        <i class="<?php echo htmlspecialchars($option['icon_class']); ?>"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($option['display_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </section>

    <div class="credit">&copy; copyright @ <?= date('Y'); ?> by <span>mr. web designer</span> | all rights reserved!</div>
</footer>