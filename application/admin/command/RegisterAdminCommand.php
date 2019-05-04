<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\command;


use app\common\service\AdminService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Exception;

class RegisterAdminCommand extends Command
{
    protected function configure()
    {
        $this->setName('admin:register')
            ->setDescription('注册管理员')
            ->addArgument('username', Argument::REQUIRED, '管理员账号')
            ->addArgument('password', Argument::REQUIRED, '管理员密码');
    }

    protected function execute(Input $input, Output $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        try {
            $admin = AdminService::Factory()->register($username, $password);
            $output->info(sprintf('添加成功! ID:%d', $admin->admin_id));
        } catch (Exception $e) {
            $output->error($e->getMessage());
        }
    }
}