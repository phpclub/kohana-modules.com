<?php

/**
 * Syncs the local queue with GitHub.
 */
class Minion_Task_Queue_Sync extends Minion_Task
{
    /**
     * Execute the task with the specified set of config
     *
     * @return boolean TRUE if task executed successfully, else FALSE
     */
    public function execute(array $config)
    {
        // Queue items that haven't been updated in the past day
        $queue = ORM::factory('queue')
            ->where('updated_at', '<', time() - Date::DAY)
            ->where('is_ignored', '=', FALSE)
            ->find_all();

        foreach ($queue as $item)
        {
            $identifier = "$item->username/$item->name";

            if ($item->sync())
            {
                Minion_CLI::write("[queue] $identifier synced.");
            }
            else
            {
                Minion_CLI::write("[queue] $identifier deleted.", 'red');
            }

            // throttle API requests
            sleep(2);
        }
    }
}
