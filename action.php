<?php

foreach (glob("action/*.php") as $filename) {
    include $filename;
}


$ROUTES->bound_post("/document/new", 'upload_document')
       ->bound_post("/document/del", 'delete_document')
       ->bound_post("/ajax/community/current", 'set_community', ['id'])
;