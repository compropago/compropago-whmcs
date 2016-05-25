<?php
/**
 * Copyright 2015 Compropago.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * Compropago views-sdk
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */
?>

<div class="cpcontainer" id="cpWrapper">
    <div class="cprow">
        <div class="cpcolumn">
            <?php echo "<h3>".$dataView['description']."</h3>"; ?>
        </div>
    </div>

    <div class="cprow">
        <div class="cpcolumn">
            <?php echo $dataView['instructions']; ?>
            <br>
            <hr>
        </div>
    </div>

    <?php if($dataView['showLogo'] == 'yes'){ ?>
        <ul>
            <?php foreach ($dataView['providers'] as $provider){ ?>
                <li>
                    <input type="radio" id="compropago_<?php echo $provider->internal_name; ?>"
                           name="compropagoProvider"
                           value="<?php echo $provider->internal_name; ?>"
                           image-label="<?php echo $provider->internal_name; ?>">

                    <label for="compropago_<?php echo $provider->internal_name; ?>">
                        <img src="<?php echo $provider->image_medium; ?>" alt="<?php echo $provider->name; ?>">
                    </label>
                </li>
            <?php } ?>
        </ul>
    <?php }else{ ?>
        <select name="compropagoProvider">
            <?php foreach ($dataView['providers'] as $provider) { ?>
                <option value="<?php echo $provider->internal_name; ?>"><?php echo $provider->name; ?></option>
            <?php } ?>
        </select>
    <?php } ?>
</div>
