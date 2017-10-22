<?php
move_uploaded_file($_FILES['media']['tmp_name'][0], '/home/isucon/isubata/webapp/public/icons/' . $_FILES['media']['name'][0]);
