import type { Metadata } from 'next';
import { CheckCircle2 } from 'lucide-react';
import { getToolCategories } from '@/lib/api';
import { SITE_NAME } from '@/lib/site';
import SubmitForm from '@/components/SubmitForm';

export const revalidate = 60;

export const metadata: Metadata = {
  title: '提交工具',
  description: `向 ${SITE_NAME} 提交你的 AI 工具，在线表单免费收录。`,
};

const CRITERIA = [
  '产品可正常访问、功能可用，非纯落地页',
  '与 AI 相关：大模型、AIGC、AI 工作流等',
  '提供清晰的产品介绍与定价信息',
  '无恶意行为（捆绑下载、欺诈营销等）',
];

export default async function SubmitPage() {
  const categories = await getToolCategories();

  return (
    <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6">
      <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">提交你的 AI 工具</h1>
      <p className="mt-3 leading-7 text-gray-600 dark:text-gray-400">
        {SITE_NAME} 欢迎开发者与厂商提交优秀的 AI 产品。填写下方表单即可，审核通过后免费收录，
        并有机会获得首页「编辑精选」与各推广位展示。
      </p>

      <div className="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
        <h2 className="font-semibold text-gray-900 dark:text-gray-100">收录标准</h2>
        <ul className="mt-4 space-y-3">
          {CRITERIA.map((c) => (
            <li key={c} className="flex items-start gap-2.5 text-sm leading-6 text-gray-600 dark:text-gray-400">
              <CheckCircle2 size={18} className="mt-0.5 shrink-0 text-emerald-500" />
              {c}
            </li>
          ))}
        </ul>
      </div>

      <div className="mt-6">
        <SubmitForm categories={categories.map((c) => ({ mid: c.mid, name: c.name }))} />
      </div>
    </div>
  );
}
