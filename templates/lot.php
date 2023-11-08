<main>
    <?=$nav?>
    <section class="lot-item container">
        <h2><?=$lots['name_lot']?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img src="<?=$lots['picture']?>" width="730" height="548" alt="">
                </div>
                <p class="lot-item__category">Категория: <span><?=$lots['name']?></span></p>
                <p class="lot-item__description"><?=$lots['description']?></p>
            </div>
            <div class="lot-item__right">
            <?php $date_end = get_dt_range($lots['date_end'])?>

                <div class="lot-item__state <?php if((int)$date_end[0] < 0):?>visually-hidden<?php endif; ?>">
                    <div class="lot-item__timer timer <?php if((int)$date_end[0] < 24):?> timer--finishing <?php endif; ?>">
                        <?= $date_end[0] ?>:<?=$date_end[1]?>
                    </div>

                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?=htmlspecialchars(formNum($price))?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span name="step"><?=htmlspecialchars(formNum($min_bet))?></span>
                        </div>
                    </div>

                    <form class="lot-item__form <?php if (!isset($_SESSION['is_auth']) || !$_SESSION['is_auth']) :?>visually-hidden<?php endif; ?>" action="lot.php?id=<?=$_GET['id']?>" method="post" autocomplete="off">
                        <p class="lot-item__form-item form__item <?php if (isset($errors['cost'])): ?>form__item--invalid <?php endif; ?>">
                            <label for="cost">Ваша ставка</label>
                            <input id="cost" type="text" name="cost" placeholder="<?=formNum($min_bet)?>" value="<?=getPostVal('cost')?>">
                            <span class="form__error"><?=$errors['cost']?></span>
                        </p>
                        <button type="submit" class="button">Сделать ставку</button>
                    </form>
                </div>
                <div class="history">
                    <h3>История ставок (<span><?=htmlspecialchars($count_bets)?></span>)</h3>
                    <table class="history__list">
                        <?php foreach ($bet_lots as $item):?>
                        
                            <tr class="history__item">
                            <td class="history__name"><?=htmlspecialchars($item['user_name'])?></td>
                            <td class="history__price"><?=htmlspecialchars(formNum($item['sum']))?></td>
                            <td class="history__time"><?=formate_date($item['date_reg'])?></td>
                        </tr>
                        <?php endforeach;?>
                        
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
