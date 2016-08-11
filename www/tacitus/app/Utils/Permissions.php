<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Utils;

class Permissions
{

    const USER_PANELS = 'user-panels';
    const VIEW_DATASETS = 'view-datasets';
    const VIEW_ALL_DATASETS = 'view-all-datasets';
    const USE_ALL_DATASETS = 'use-all-datasets';
    const SUBMIT_DATASETS = 'submit-dataset';
    const DELETE_DATASETS = 'delete-datasets';
    const SELECT_FROM_DATASETS = 'select-from-datasets';
    const VIEW_SELECTIONS = 'view-selections';
    const REMOVE_SELECTIONS = 'remove-selections';
    const DOWNLOAD_SELECTIONS = 'download-selections';
    const USE_TOOLS = 'use-tools';
    const INTEGRATE_DATASETS = 'integrate-datasets';
    const VIEW_JOBS = 'view-jobs';
    const VIEW_ALL_JOBS = 'view-all-jobs';
    const ADMINISTER = 'administer-system';

}
