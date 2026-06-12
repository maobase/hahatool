import { PackageOpen } from 'lucide-react';

export default function EmptyState({
  title = '暂无数据',
  hint = '如果你刚部署完成，请确认已安装 Typecho、启用 Restful 插件并导入示例数据（bash scripts/seed.sh）。',
}: {
  title?: string;
  hint?: string;
}) {
  return (
    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-16 text-center">
      <PackageOpen size={40} className="text-gray-300" />
      <p className="mt-4 font-medium text-gray-700 dark:text-gray-300">{title}</p>
      <p className="mt-2 max-w-md text-sm leading-6 text-gray-500">{hint}</p>
    </div>
  );
}
