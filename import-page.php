<div class="wrap mcode-commerceml">

    <h1 class="wp-heading-inline"><?= __('Import prices from CommerceML 1.0', 'mcode-commerceml-import') ?></h1>

    <form method="POST" enctype="multipart/form-data">

        <table>

            <tr><td colspan="3"><h2><?= __('Import Settings', 'mcode-commerceml-import') ?></h2></td></tr>
            <tr>
                <td width="500"><label><?= __('Currency transfer, rate:', 'mcode-commerceml-import') ?></label></td>
                <td colspan="2"><input type="number" name="course" value="<?= $course ?>" step="0.01" /></td>
            </tr>
            <tr>
                <td><label><?= __('Round to the number of decimal places:', 'mcode-commerceml-import') ?></label></td>
                <td colspan="2"><input type="number" name="precision" value="<?= $precision ?>" /></td>
            </tr>
            <tr><td colspan="3"><h2><?= __('Import file', 'mcode-commerceml-import') ?></h2></td></tr>
            <tr><td colspan="3"><input type="file" name="import" accept="xml" /></td></tr>
            <tr><td></td><td colspan="2"><input type="submit" name="submit" class="button button-primary" value="<?= __('Import', 'mcode-commerceml-import') ?>"></td></tr>

            <?php if (!empty($result)): ?>

                <tr><td colspan="3"><h2><?= __('Import result', 'mcode-commerceml-import') ?></h2></td></tr>
                <tr>
                    <th width="500"><?= __('Product', 'mcode-commerceml-import') ?></th>
                    <th width="100"><?= __('Old price', 'mcode-commerceml-import') ?></th>
                    <th width="100"><?= __('New price', 'mcode-commerceml-import') ?></th>
                </tr>

                <?php foreach ($result as $item): ?>
                    <tr>
                        <td><a href="<?= $item['link'] ?>" title="<?= $item['title'] ?>" target="_blank"><?= $item['title'] ?></a></td>
                        <td><?= $item['old'] ?></td>
                        <td><?= $item['price'] ?></td>
                    </tr>
                <?php endforeach;?>

            <?php endif; ?>

        </table>

    </form>

</div>