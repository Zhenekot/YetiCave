<main>
  <?= $nav ?>
  <section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
      <?php foreach ($bet_lots as $item): ?>
        <?php
        $class = "";
        $class_row = "";
        $status_lot = "";
        if (strtotime($item['lot_date']) < strtotime( date("y-m-d"))) {
          if (isset($item['is_win']) && $item['is_win']) {
            $status_lot = "Ставка выиграла";
            $class_row = "rates__item--win";
            $class = "timer--win";
          } else {
            $class_row = "rates__item--end";
            $status_lot = "Торги окончены";
            $class = "timer--end";
          }
        }
        ?>
        <tr class="rates__item <?= $class_row ?>">
          <td class="rates__info">
            <div class="rates__img">
              <img src="<?= $item['pic_lot'] ?>" width="54" height="40" alt="">
            </div>
            <div>
              <h3 class="rates__title"><a href="lot.php?id=<?= $item['lot_id'] ?>">
                  <?= htmlspecialchars($item['lot_name']) ?>
                </a></h3>
              <p>
                <?= ($status_lot === "Ставка выиграла") ? htmlspecialchars($item['creator_contact']) : "" ?>
              </p>
            </div>
          </td>
          <td class="rates__category">
            <?= htmlspecialchars($item['category_name']) ?>
          </td>
          <td class="rates__timer">
            <div
              class="timer <?= $class ?> <?php if (((int) $date_end[0] < 24) && ($status_lot === "")): ?> timer--finishing <?php endif; ?>">
              <?php $date_end = ($status_lot === "") ? get_dt_range($item['lot_date']) : $status_lot ?>
              <?php if ($status_lot === ""): ?>
                <?= "$date_end[0]:$date_end[1]" ?>
              <?php else: ?>
                <?= htmlspecialchars($status_lot) ?>
              <?php endif; ?>
            </div>
          </td>
          <td class="rates__price">
            <?= htmlspecialchars(formNum($item['sum'])) ?>
          </td>
          <td class="rates__time">
            <?= htmlspecialchars(formate_date($item['date_reg'])) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </section>
</main>