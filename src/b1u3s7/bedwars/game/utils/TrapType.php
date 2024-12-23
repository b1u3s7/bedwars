<?php

namespace b1u3s7\bedwars\game\utils;

class TrapType
{
    public static int $ALARM_TRAP = 0; // removes invisibility, chat message + sounds
    public static int $MINING_TRAP = 1; // 15 seconds mining fatigue
    public static int $BLINDNESS_TRAP = 2; // blindness & slowness for 5 seconds
    public static int $COUNTER_TRAP = 3; // speed & jump for 15 seconds for allies
}