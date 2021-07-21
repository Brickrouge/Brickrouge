<?php

namespace Brickrouge;

echo new Alert("You in danger girl!", [
    Alert::HEADING => "Heading <here>",
    Alert::CONTEXT => Alert::CONTEXT_DANGER,
]);
