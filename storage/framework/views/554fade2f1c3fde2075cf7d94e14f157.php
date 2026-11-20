<?php
    $backgroundFile = 'images/system-background-selected.png';
    $backgroundUrl = asset($backgroundFile).'?v='.filemtime(public_path($backgroundFile));
?>
<link rel="preload" as="image" type="image/png" href="<?php echo e($backgroundUrl); ?>" fetchpriority="high">
<style>
    :root { --campus-background: url("<?php echo e($backgroundUrl); ?>"); }
</style>
<?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/components/background-assets.blade.php ENDPATH**/ ?>